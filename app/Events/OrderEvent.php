<?php

namespace App\Events;

use App\Models\RestaurantRoom;
use App\Models\User;
use App\Models\Order;
use App\Models\Table;
use App\Models\Restaurant;
use App\Services\NotificationService;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class OrderEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    private Order $order;
    private $notification;

    private $restaurant;
     public function __construct($order)
    {
        $this->order = $order;
    }
    public function store(){
        try{
            $tableId = $this->order->table_id;
            $roomId = $this->order->room_id;
            if($tableId){
                $restaurantId = Table::where('id', $tableId)->first()->restaurant_id;
                $this->restaurant = Restaurant::find($restaurantId);
            }
            if($roomId){
                $restaurantId = RestaurantRoom::where('id', $roomId)->first()->restaurant_id;
                $this->restaurant = Restaurant::find($restaurantId);
            }
            $notificationOrder = new NotificationService();
            $receivers = [$this->restaurant->user_id, $this->restaurant->manager];
            $newNotification = $notificationOrder->store(1, $this->order->id, 'users',auth()->user()->id,$receivers);
            $this->notification = $newNotification;
        }catch(\Exception $e){
            return response()->json(['errors'=>$e->getMessage()],400);
        }
        
    }
    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        // $restaurant = $this->order->with(relations: 'restaurant')->first();
        
        return [
            new PrivateChannel('order.'.$this->restaurant->id),
        ];
    }
    public function broadcastWith()
    {
        return [
            'id' => $this->order->id,
            'notification' => $this->notification,
        ];
    }
    public function broadcastAs(): string
    {
        return 'order.created';
    }
}
