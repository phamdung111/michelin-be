<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
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
            return response()->json(['status'=> 'success'],200);
        }catch (\Exception $e) {
            return response()->json(['errors'=> $e->getMessage()],200);
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
                ->with('restaurant')
                ->with('restaurant.images')
                ->orderBy('order_time')
                ->paginate(20);
 
            $ordersData = $orders->map(function($order){
                return [
                    'id'=> $order->id,
                    'time'=> Carbon::parse($order->order_time)->format('H:i Y-m-d'),
                    'status'=> $order->status,
                    'restaurant' => [
                        'id'=> $order->restaurant->id,
                        'name'=> $order->restaurant->name,
                        'phone'=> $order->restaurant->phone,
                        'address'=> $order->restaurant->address,
                        'images'=> $order->restaurant->images->map(function ($image){
                            return Storage::url($image->image);
                        }),
                    ]
                ];
            });
            return response()->json([
                'orders' => $ordersData,
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
