<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

use Symfony\Component\Process\Process;

/**
 * Handles the asynchronous execution handoff for The Writers Room Live pipelines.
 * 
 * This queueable job ensures the potentially long-running multi-agent conversational matrix
 * is isolated from the primary Laravel web request lifecycle to prevent timeout collisions.
 */
class RunWritersRoomJob implements ShouldQueue
{
    use Queueable;

    /**
     * The target database primary key matching the active execution Run.
     * 
     * @var int
     */
    public int $runId;

    /**
     * Binds the specific runtime payload upon job instantiation.
     * 
     * @param int $runId The SQL primary key of the tracked conversational run.
     */
    public function __construct(int $runId)
    {
        // We explicitly map the identifier so the subsequent python layer
        // can query the exact state constraints it requires to boot.
        $this->runId = $runId;
    }

    /**
     * Fires the deterministic python framework matrix asynchronously.
     * 
     * @return void
     */
    public function handle(): void
    {
        // We instantiate the robust Symfony Process shell wrapper to cleanly
        // pipe arguments into the isolated native python script layer securely.
        $executionProcess = new Process(['python3', base_path('bin/agent_runner.py'), (string) $this->runId]);
        
        // Complex stacked LLM iterations require vast temporal overhead thresholds based on APIs.
        // We suspend standard limits, granting a 1-hour buffer.
        $executionProcess->setTimeout(3600); 
        
        // Trigger the blocking CLI command. The queue worker will suspend routing until the script ends.
        $executionProcess->run();
    }
}
