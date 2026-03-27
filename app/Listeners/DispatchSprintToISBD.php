<?php

namespace App\Listeners;

use App\Events\SprintReady;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class DispatchSprintToISBD implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(SprintReady $event): void
    {
        Log::info("DispatchSprintToISBD Listener: Detected Sprint {$event->sprint->id} is ready. Bridging to ISBD.");
        
        Artisan::call('bridge:dispatch-sprint', [
            'sprint_id' => $event->sprint->id,
        ]);
    }
}
