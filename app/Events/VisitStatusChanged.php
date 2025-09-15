<?php

namespace App\Events;

use App\Models\Shift;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VisitStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $shift;
    public $newStatus;
    public $visit;

    /**
     * Create a new event instance.
     */
    public function __construct(Shift $shift, string $newStatus, $visit = null)
    {
        $this->shift = $shift;
        $this->newStatus = $newStatus;
        $this->visit = $visit;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('client-schedule.' . $this->shift->client_id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'VisitStatusChanged';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'shift_id' => $this->shift->id,
            'new_status' => $this->newStatus,
            'visit_data' => $this->visit ? [
                'clock_in_time' => $this->visit->clock_in_time,
                'clock_out_time' => $this->visit->clock_out_time,
            ] : null,
        ];
    }
}