<?php

namespace App\Events;

use App\Models\Order;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    private Order $order;
     public function __construct($order)
    {
        $this->order = $order;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $restaurantId = $this->order->with('restaurant')->first()->restaurant->id;
        return [
            new PrivateChannel('order.'.$restaurantId),
        ];
    }
    public function broadcastWith()
    {
        return [
            'id' => $this->order->id,
            'user_id' => $this->order->user_id,
            'created_at' => $this->order->created_at,
            'order'=>$this->order,
        ];
    }
    public function broadcastAs(): string
{
    return 'order.created';
}
}
