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
            'restaurantId'=>['required','exists:restaurants,id'],
            'time'=>['required','date_format:Y-m-d H:i'],
            'guests'=>['required'],
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        try {
            $order = new Order();
            $order->restaurant_id = $request->restaurantId;
            $order->order_time =  $request->time;
            $order->user_id = auth()->user()->id;
            $order->guest = $request->guests;
            $order->status = 'pending';
            $order->save();
            return response()->json(['status'=> 'success'],200);
        }catch (\Exception $e) {
            return response()->json(['errors'=> $e->getMessage()],200);
        }

    }

    public function orderByRestaurantToday()
    {   
        $restaurantOwn = Restaurant::where('user_id', auth()->user()->id)->get();
        $today = date('Y-m-d');

        $orders = Order::whereDate('order_time', $today)
            ->whereIn('restaurant_id', $restaurantOwn->pluck('id'))
            ->with('user')
            ->with('restaurant')
            ->with('restaurant.images')
            ->orderBy('order_time')
            ->paginate(20);
        $orderData = $orders->map(function ($order){
            return [
                'id'=> $order->id,
                'guests'=> $order->guest,
                'status'=> $order->status,
                'order_time'=> $order->order_time,
                'restaurant'=> [
                    'id' => $order->restaurant->id,
                    'name'=> $order->restaurant->name,
                    'address'=> $order->restaurant->address,
                    'phone'=> $order->restaurant->phone,
                    'description' => $order->restaurant->description,
                    'images'=> $order->restaurant->images->map(function ($image) {
                            return 
                                Storage::url($image->image);
                        }),
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
            'last_page' => $orders->lastPage()
            ],200);
    }
    public function countOrdersToday(Request $request){
        $restaurantOwn = Restaurant::where('user_id', auth()->user()->id)->get();
        $today = date('Y-m-d');
        $countOrders = Order::whereDate('order_time', $today)
            ->whereIn('restaurant_id', $restaurantOwn->pluck('id'))
            ->count();
        return response()->json($countOrders,200);
    }

    public function oldOrderByRestaurant(){
        $restaurantOwn = Restaurant::where('user_id', auth()->user()->id)->get();
        $today = date('Y-m-d');

        $orders = Order::whereDate('order_time', '<', $today)
            ->whereIn('restaurant_id', $restaurantOwn->pluck('id'))
            ->with('user')
            ->with('restaurant')
            ->with('restaurant.images')
            ->orderBy('order_time')
            ->paginate(20);
        $orderData = $orders->map(function ($order){
            return [
                'id'=> $order->id,
                'guests'=> $order->guest,
                'status'=> $order->status,
                'order_time'=> $order->order_time,
                'restaurant'=> [
                    'id' => $order->restaurant->id,
                    'name'=> $order->restaurant->name,
                    'address'=> $order->restaurant->address,
                    'phone'=> $order->restaurant->phone,
                    'description' => $order->restaurant->description,
                    'images'=> $order->restaurant->images->map(function ($image) {
                            return 
                                Storage::url($image->image);
                        }),
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
            'last_page' => $orders->lastPage()
            ],200);
    }

    public function changeStatus(Request $request){
        $validator = Validator::make($request->all(), [
            'orderId'=> 'required',
            'status'=> 'required|in:done,pending,cancel',
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

    
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
