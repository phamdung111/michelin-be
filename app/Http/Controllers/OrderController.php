<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

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

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
