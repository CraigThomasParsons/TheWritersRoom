<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\Log;

class InitializeProjectCodebaseJob implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     * We allow retrying since OpenClaw is heavily rate limited.
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public $backoff = [3600, 7200]; // Wait 1 hr, then 2 hrs

    protected string $projectPath;

    /**
     * Create a new job instance.
     */
    public function __construct(string $projectPath)
    {
        $this->projectPath = $projectPath;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("[*] InitializeProjectCodebaseJob starting for Path: {$this->projectPath}");

        if (is_dir($this->projectPath)) {
            Log::info("[+] Directory already exists, skipping OpenClaw initialization: {$this->projectPath}");
            return;
        }

        $projectName = basename($this->projectPath);
        $prompt = "Create a new project repository for {$projectName}. Initialize a git repository. Set the remote to my Github CraigThomasParsons account with the repository name {$projectName}. Push the initial empty commit. Then, clone that repository to this local machine at exactly the path {$this->projectPath}";

        $process = new Process(['openclaw', 'agent', '--agent', 'main', '--message', $prompt]);
        
        $process->setEnv([
            'PATH' => getenv('PATH') ?: '/usr/local/bin:/usr/bin:/bin',
            'HOME' => getenv('HOME') ?: '/home/craigpar',
        ]);
        
        $process->setTimeout(300); // 5 minutes

        Log::info("[*] Executing OpenClaw CLI...");
        $process->run();

        $output = $process->getOutput();
        $errorOutput = $process->getErrorOutput();
        $combinedOutputLower = strtolower($output . $errorOutput);

        Log::info($output);
        if ($errorOutput) {
            Log::error($errorOutput);
        }

        if (
            str_contains($combinedOutputLower, 'rate limit reached') ||
            str_contains($combinedOutputLower, 'quota exceeded') ||
            str_contains($combinedOutputLower, '429 too many')
        ) {
            Log::warning("[-] OpenClaw hit an API rate limit or quota while scaffolding " . $this->projectPath . ".");
            
            // Calculate exact seconds until the next 2:00 AM
            $nextTwoAm = now()->setTime(2, 0);
            if ($nextTwoAm->isPast()) {
                $nextTwoAm->addDay();
            }
            $delayInSeconds = now()->diffInSeconds($nextTwoAm);
            
            Log::warning("[*] Releasing job back to queue. It will automatically retry at precisely 2:00 AM ({$delayInSeconds} seconds from now).");
            $this->release($delayInSeconds);
            return;
        }

        if (!$process->isSuccessful()) {
            Log::error("[-] OpenClaw failed to initialize the repository: {$this->projectPath}");
            $this->fail(new ProcessFailedException($process));
            return;
        }

        if (!is_dir($this->projectPath)) {
            Log::error("[-] OpenClaw succeeded but the directory {$this->projectPath} was not created.");
            $this->fail(new \Exception("Directory {$this->projectPath} was not created by OpenClaw."));
            return;
        }

        Log::info("[+] OpenClaw successfully initialized the repository at {$this->projectPath}");
    }
}
