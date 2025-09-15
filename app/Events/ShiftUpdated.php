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

class ShiftUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The shift instance.
     *
     * @var \App\Models\Shift
     */
    public $shift;

    /**
     * Create a new event instance.
     */
    public function __construct(Shift $shift)
    {
        $this->shift = $shift;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        // This creates a private channel named, for example, 'client-schedule.123'
        // where 123 is the ID of the client associated with the shift.
        // This ensures only the correct client receives the notification.
        return [
            new PrivateChannel('client-schedule.' . $this->shift->client_id),
        ];
    }
}