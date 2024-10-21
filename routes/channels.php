<?php

use App\Models\Restaurant;
use App\Models\User;
use App\Models\Order;
use App\Broadcasting\OrderChannel;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
Broadcast::channel('my-channel', function ($user) {  
    return true;  
});
Broadcast::channel('user.{userId}', function ($user, $userId) {
  return $user->id === $userId;
});


Broadcast::channel('order.{restaurantOrder}', function (User $user, int $restaurantOrder) {
    Log::info('User ID: ' . $user->id);
    return true;
    // $userRestaurant = Restaurant::find($restaurantOrder);
    // return $userRestaurant && $user->id === $userRestaurant->user_id;
});
