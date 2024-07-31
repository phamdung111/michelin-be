<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\RestaurantRoom;
use App\Models\Table;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class TableOrderController extends Controller
{
    public function tables0rRoomsByOrderTime(Request $request){
        $time = $request->time;
        $restaurantId = $request->restaurantId;
        $tablesRestaurant = Table::where("restaurant_id", $restaurantId)->get();
        $roomsRestaurant = RestaurantRoom::where("restaurant_id", $restaurantId)->get();
        $availableTables = $tablesRestaurant->filter(function ($table) use ($time) {
            $orders = $table->orders()
                ->where('order_time', '>=', $time)
                ->where('order_time', '<=', $time)
                ->where('status', 'booking')
                ->get();
            return $orders->isEmpty();
        });
        $availableRooms = $roomsRestaurant->filter(function ($room) use ($time) {
            $orders = $room->orders()
                ->where('order_time', '>=', $time)
                ->where('order_time', '<=', $time)
                ->where('status', 'booking')
                ->get();
            return $orders->isEmpty();
        });
        $tableNumbers = $availableTables->pluck('table_number')->toArray();
        $roomNumbers = $availableRooms->pluck('room_number')->toArray();
        return response()->json(['tables' => $tableNumbers, 'rooms' => $roomNumbers], 200);
    }
}
