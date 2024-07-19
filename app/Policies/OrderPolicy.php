<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Order;
use App\Models\Restaurant;

class OrderPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }
    public function changeStatus(User $user, Order $order){
        $restaurant = Restaurant::findOrFail("id", $order->restaurant_id);
        if($order->user_id === auth()->user()->id){
            return true;
        }
        elseif($restaurant->user_id === auth()->user()->id) {
            return true;
        }else{
            return false;
        }
    }
}
