<?php

namespace App\Http\Controllers;

use App\Events\OrderEvent;
use App\Models\Order;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'time'=>['required','date_format:Y-m-d H:i'],
            'guests'=>['required'],
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        try {
            $order = new Order();
            $order->table_id = $request->tableId;
            $order->room_id = $request->roomId;
            $order->order_time =  $request->time;
            $order->user_id = auth()->user()->id;
            $order->guests = $request->guests;
            $order->status = 'booking';
            $order->save();
            $eventOrder = new OrderEvent($order);
            $eventOrder->store();
            event ($eventOrder);
            return response()->json(['status'=> 'success'],200);
        }catch (\Exception $e) {
            return response()->json(['errors'=> $e->getMessage()],status: 400);
        }
    }

    
    public function changeStatus(Request $request){
        $validator = Validator::make($request->all(), [
            'orderId'=> 'required',
            'status'=> 'required|in:done,booking,cancel,serving',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try{
            $order = Order::where('id',$request->orderId)
                ->first();
            $restaurantOrdered = Restaurant::where('id', $order->restaurant_id)->first();
            if($order->user_id === auth()->user()->id || $restaurantOrdered->user_id === auth()->user()->id){
                $order->status = $request->status;
                $order->save();
                return response()->json(true,200);
            }else{
                return response()->json(false,403);
            }

        }catch(\Exception $e){
            return response()->json(['errors'=> $e->getMessage()],400);
        }
    }

    public function orderByUser(){
        try{
            $today = date('Y-m-d H:i');

            $orders = Order::where('order_time','>=',$today)
                ->where('user_id',auth()->user()->id)
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
                'time'=> $order->order_time,
                'table'=> $order->tables? $order->tables->table_number : null,
                'room'=> $order->rooms ? $order->rooms->room_number : null,
                'restaurant' => [
                    'id' => $restaurant->id,
                    'name' => $restaurant->name,
                    'address' => $restaurant->address,
                    'phone' => $restaurant->phone,
                    'email' => $restaurant->email,
                    'avatar' => Storage::url($restaurant->avatar)
                ],
            ];
        });
            return response()->json([
                'orders' => $orderData,
                'current_page' => $orders->currentPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
                'last_page' => $orders->lastPage()
            ],200);
        }catch(\Exception $e){
            return response()->json(['errors'=> $e->getMessage()],400);
        }
        
    }
    public function cancelStatus(Request $request){
        $validator = Validator::make($request->all(),[
            'orderId' =>'required',
        ]);
        if($validator->fails()){
            return response()->json(['errors' => $validator->errors()], 422);
        };
        try{
            $order = Order::findOrFail($request->orderId);
            if($order->user_id === auth()->user()->id){
                $order->status = 'cancel';
                $order->save();
                return response()->json(true,200);
            }else{
                return response()->json(['message'=>'authorization'],403);
            }
        }catch(\Exception $e){
            return response()->json(['errors'=> $e->getMessage()],400);
        }
    }
    public function destroy(string $id)
    {
        $order = Order::findOrFail($id);
        try{
            $order->delete();
            return response()->json(true,200);
        }catch(\Exception $e){
            return response()->json(['errors'=> $e->getMessage()],400);
        }
    }
}
