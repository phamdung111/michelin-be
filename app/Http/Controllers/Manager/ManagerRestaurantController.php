<?php

namespace App\Http\Controllers\Manager;

use App\Models\Order;
use App\Models\Table;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use App\Models\RestaurantRoom;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class ManagerRestaurantController extends Controller
{
    public function countOrdersToday(Request $request){
        $user_id = auth()->user()->id;
        $restaurantIds = Restaurant::where('manager', $user_id)->pluck('id');
        $tableIds = Table::whereIn('restaurant_id', $restaurantIds)->pluck('id');
        $roomIds = RestaurantRoom::whereIn('restaurant_id', $restaurantIds)->pluck('id');

        $today = date('Y-m-d');
        
        $countOrders = Order::whereDate('order_time', $today)
        ->where(function($query) use ($tableIds, $roomIds) {
            $query->whereIn('table_id', $tableIds)
                ->orWhereIn('room_id', $roomIds);
        })->count();
        return response()->json($countOrders);
        
    }
    public function ordersRestaurantToday()
    {   
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $user_id = auth()->user()->id;
        $restaurant = Restaurant::where('manager', $user_id)->first();
        $tableIds = Table::where('restaurant_id', $restaurant->id)->pluck('id');
        $roomIds = RestaurantRoom::where('restaurant_id', $restaurant->id)->pluck('id');

        $today = date('Y-m-d');

        $orders = Order::whereDate('order_time','=', $today)
            ->where(function($query) use ($tableIds, $roomIds) {
                $query->whereIn('table_id', $tableIds)
                    ->orWhereIn('room_id', $roomIds);
            })
            ->with('user')
            ->with('tables')
            ->with('rooms')
            ->with('restaurant')
            ->orderBy('order_time')
            ->paginate(20);
        $orderData = $orders->map(function ($order){
            $restaurant = null;
            if($order->tables) {
                $restaurant = Restaurant::where('id', $order->tables->restaurant_id)
                    ->first();
            }
            if($order->rooms) {
                $restaurant = Restaurant::where('id', $order->rooms->restaurant_id)
                    ->first();
            }
            return [
                'id'=> $order->id,
                'guests'=> $order->guests,
                'status'=> $order->status,
                'order_time'=> $order->order_time,
                'table'=> $order->tables,
                'room'=> $order->rooms,
                'restaurant' => [
                    'id' => $restaurant->id,
                    'name' => $restaurant->name,
                    'address' => $restaurant->address,
                    'phone' => $restaurant->phone,
                    'email' => $restaurant->email,
                    'avatar' => Storage::url($restaurant->avatar)
                ],
                'userOrdered'=> [
                    'id'=> $order->user->id,
                    'name'=> $order->user->name,
                    'email'=> $order->user->email,
                    'phone'=> $order->user->phone,
                    'location' => $order->user->location,
                    'avatar' => Storage::url($order->user->avatar),
                ]
            ];
        });
        return response()->json([
            'orders' => $orderData,
            'current_page' => $orders->currentPage(),
            'per_page' => $orders->perPage(),
            'total' => $orders->total(),
            'last_page' => $orders->lastPage(),
            'today' => $today

            ],200);
    }
    public function ordersRestaurantOld(){
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $user_id = auth()->user()->id;
        $restaurant = Restaurant::where('manager', $user_id)->first();
        $tableIds = Table::where('restaurant_id', $restaurant->id)->pluck('id');
        $roomIds = RestaurantRoom::where('restaurant_id', $restaurant->id)->pluck('id');

        $today = date('Y-m-d');

        $orders = Order::whereDate('order_time', '<', $today)
                ->where(function($query) use ($tableIds, $roomIds) {
                    $query->whereIn('table_id', $tableIds)
                    ->orWhereIn('room_id', $roomIds);
            })
            ->with('user')
            ->with('tables')
            ->with('rooms')
            ->with('restaurant')
            ->orderBy('order_time')
            ->paginate(20);
        $orderData = $orders->map(function ($order){
            $restaurant = null;
            if($order->tables) {
                $restaurant = Restaurant::where('id', $order->tables->restaurant_id)
                    ->first();
            }
            if($order->rooms) {
                $restaurant = Restaurant::where('id', $order->rooms->restaurant_id)
                    ->first();
            }
            return [
                'id'=> $order->id,
                'guests'=> $order->guests,
                'status'=> $order->status,
                'order_time'=> $order->order_time,
                'table'=> $order->tables,
                'room'=> $order->rooms,
                'restaurant' => [
                    'id' => $restaurant->id,
                    'name' => $restaurant->name,
                    'address' => $restaurant->address,
                    'phone' => $restaurant->phone,
                    'email' => $restaurant->email,
                    'avatar' => Storage::url($restaurant->avatar)
                ],
                'userOrdered'=> [
                    'id'=> $order->user->id,
                    'name'=> $order->user->name,
                    'email'=> $order->user->email,
                    'phone'=> $order->user->phone,
                    'location' => $order->user->location,
                    'avatar' => Storage::url($order->user->avatar),
                ]
            ];
        });
        return response()->json([
            'orders' => $orderData,
            'current_page' => $orders->currentPage(),
            'per_page' => $orders->perPage(),
            'total' => $orders->total(),
            'last_page' => $orders->lastPage(),

            ],200);
    }
    public function ordersRestaurantFuture(){
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $user_id = auth()->user()->id;
        $restaurant = Restaurant::where('manager', $user_id)->first();
        $tableIds = Table::where('restaurant_id', $restaurant->id)->pluck('id');
        $roomIds = RestaurantRoom::where('restaurant_id', $restaurant->id)->pluck('id');

        $today = date('Y-m-d');

        $orders = Order::whereDate('order_time', '>', $today)
            ->where(function($query) use ($tableIds, $roomIds) {
                $query->whereIn('table_id', $tableIds)
                    ->orWhereIn('room_id', $roomIds);
            })
            ->with('user')
            ->with('tables')
            ->with('rooms')
            ->with('restaurant')
            ->orderBy('order_time')
            ->paginate(20);
        $orderData = $orders->map(function ($order){
            $restaurant = null;
            if($order->tables) {
                $restaurant = Restaurant::where('id', $order->tables->restaurant_id)
                    ->first();
            }
            if($order->rooms) {
                $restaurant = Restaurant::where('id', $order->rooms->restaurant_id)
                    ->first();
            }
            return [
                'id'=> $order->id,
                'guests'=> $order->guests,
                'status'=> $order->status,
                'order_time'=> $order->order_time,
                'table'=> $order->tables,
                'room'=> $order->rooms,
                'restaurant' => [
                    'id' => $restaurant->id,
                    'name' => $restaurant->name,
                    'address' => $restaurant->address,
                    'phone' => $restaurant->phone,
                    'email' => $restaurant->email,
                    'avatar' => Storage::url($restaurant->avatar)
                ],
                'userOrdered'=> [
                    'id'=> $order->user->id,
                    'name'=> $order->user->name,
                    'email'=> $order->user->email,
                    'phone'=> $order->user->phone,
                    'location' => $order->user->location,
                    'avatar' => Storage::url($order->user->avatar),
                ]
            ];
        });
        return response()->json([
            'orders' => $orderData,
            'current_page' => $orders->currentPage(),
            'per_page' => $orders->perPage(),
            'total' => $orders->total(),
            'last_page' => $orders->lastPage(),
            'today'=> $today
            ],200);
    }
}
