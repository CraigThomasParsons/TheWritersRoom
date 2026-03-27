<?php

namespace App\Livewire\Chatroom;

use Livewire\Component;
use App\Models\Room;
use App\Models\Message;
use App\Models\Agent;
use App\Models\Run;
use App\Jobs\RunWritersRoomJob;

/**
 * The primary Livewire frontend controller driving The Writers Room Live interface.
 * 
 * This component binds the raw SQL intelligence data payload securely into the Blade DOM.
 * It strictly handles deterministic room routing, intelligence stream rendering, and job dispatching.
 */
class ChatLayout extends Component
{
    /**
     * Tracks the globally active environment namespace mapped to the user's viewport.
     * 
     * @var int|null
     */
    public ?int $activeRoomId = null;

    /**
     * Compiles the full Tri-Panel state requirement matrix for the live UI polling engine.
     * 
     * @return \Illuminate\View\View
     */
    public function render()
    {
        // Pre-cache the entire topological scope of existing navigation partitions.
        $allRooms = Room::all();
        
        // We instantiate structural defaults to prevent exceptions on cold UI boots.
        $liveMessages = collect();
        $systemAgents = Agent::all();
        $latestRunState = null;

        // Ensure we load targeting payload strictly for the validated interactive partition.
        if ($this->activeRoomId !== null) {
            
            // Acquire chronological transmission chains bound logically to the viewport context.
            $liveMessages = Message::where('room_id', $this->activeRoomId)
                ->orderBy('created_at', 'asc')
                ->get();
                
            // Intercept the most recent background daemon tracker flag relative to the room boundaries.
            $latestRunState = Run::where('room_id', $this->activeRoomId)
                ->latest()
                ->first();
                
        // Fallback safely to initialize the viewport dynamically if no partition was explicitly cast yet.
        } elseif ($allRooms->isNotEmpty()) {
            
            // Silently bind the active cursor to the foremost structural row on cold loads.
            $this->activeRoomId = $allRooms->first()->id;
            
            // Execute the initial historical transmission sweep.
            $liveMessages = Message::where('room_id', $this->activeRoomId)
                ->orderBy('created_at', 'asc')
                ->get();
                
            // Fetch the primary execution flag to synchronize UI polling status bars.
            $latestRunState = Run::where('room_id', $this->activeRoomId)
                ->latest()
                ->first();
        }

        // Output the entire unified view matrix back into the reactive Blade renderer.
        return view('livewire.chatroom.chat-layout', [
            'rooms' => $allRooms,
            'messages' => $liveMessages,
            'agents' => $systemAgents,
            'activeRun' => $latestRunState
        ]);
    }

    /**
     * Captures GUI interaction hooks to pivot the active intelligence stream layer.
     * 
     * @param int $roomId The strict primary database key corresponding to the desired room node.
     * @return void
     */
    public function selectRoom(int $roomId): void
    {
        // Push the requested namespace vector globally into the internal Livewire tracker.
        $this->activeRoomId = $roomId;
    }

    /**
     * Commences a fresh operational sequence, triggering the Python intelligence scripts natively.
     * 
     * @return void
     */
    public function generateOutput(): void
    {
        // Abort background invocation strictly if the UI layer lacks bounded operational scopes.
        if ($this->activeRoomId === null) {
            return;
        }

        // Persist a fresh internal baseline anchor to track the lifecycle status of the new pipeline.
        $executionRun = Run::create([
            'room_id' => $this->activeRoomId,
            'status' => 'running',
            'goal' => 'Generate Writers Room Output'
        ]);

        // Hand off the payload physically to the system queue workers for native Python bridging.
        RunWritersRoomJob::dispatch($executionRun->id);
    }
}
