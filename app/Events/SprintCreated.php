<?php

namespace App\Events;

use App\Models\Sprint;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SprintCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Sprint $sprint;

    /**
     * Create a new event instance.
     */
    public function __construct(Sprint $sprint)
    {
        $this->sprint = $sprint;
    }
}
