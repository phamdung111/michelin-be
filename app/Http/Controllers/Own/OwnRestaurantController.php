<?php

namespace App\Http\Controllers\Own;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use App\Models\Table;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use App\Models\RestaurantRoom;
use App\Models\RestaurantImage;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\RestaurantImageController;

class OwnRestaurantController extends Controller
{
    public function checkManager(Request $request) {
       $validator = Validator::make($request->all(),[
            'email' => [
                'required',
                'email',
            ],
            'restaurantId'=>['required']
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $email = $request->email;
        $manager = User::where('email',$email)
                    ->with('role')
                    ->first();
        $restaurantId = $request->restaurantId;
        $restaurant = Restaurant::where('id',$restaurantId)
                        ->first();
        if($restaurant->user_id !== auth()->user()->id){
            return response()->json(['permission'],403);
        }
        if(!$manager || $manager->role->id !== 4){
            return response()->json(['message' => 'Not found user with the email!'],404);
        }
        else{
            return response()->json([
                'id' => $manager->id,
                'name' => $manager->name,
                'email' => $manager->email,
                'avatar' => Storage::url($manager->avatar),
                'phone' => $manager->phone,
            ],200);
        }
    }
    public function update(Request $request, RestaurantImageController $restaurantImageController)
    {
        $restaurant = Restaurant::findOrFail($request->input('id'));
        $response = Gate::inspect('update', $restaurant);
        if ($response->allowed()) {
            if(!$restaurant) {
                return response()->json(['error'=> 'Not found the restaurant'], 400);
            }else{
                try{
                    $restaurant->name = $request->name ?? $restaurant->name;
                    $restaurant->description = $request->description ?? $restaurant->description;
                    $restaurant->phone = $request->phone ?? $restaurant->phone;
                    $restaurant->address = $request->address ?? $restaurant->address;
                    $restaurant->status = $request->status ?? $restaurant->status;
                    $restaurant->email = $request->email ?? $restaurant->email;
                    if($request->avatar){
                        $oldAvatar = $restaurant->avatar;
                        $newAvatar = $request->avatar;
                        $nameAvatar = $newAvatar->hashName();
                        Storage::putFileAs('images', $newAvatar, $nameAvatar);
                        Storage::delete($oldAvatar);
                        $restaurant->avatar = '/images/'. $nameAvatar;
                    }
                    if ($request->has('allow_booking')) {
                       $restaurant->allow_booking = $request->allow_booking === "1" ? 1 : 0;
                    }
                    if ($request->has('images_removed')) {
                        $imagesRemoved = $request->images_removed;
                        $imageIds = explode(',', $imagesRemoved);
                        foreach ($imageIds as $imageId) {
                            $restaurantImageController->destroy((int)$imageId);
                        }
                    }
                    if ($request->has('images')) {
                       foreach ($request->file('images') as $image) {
                            $restaurantImageController->store($image, $restaurant->id);
                        }
                    }
                    if( $request->has('totalTables') ){
                        $oldTotalTables = $restaurant->total_tables;
                        $newTotalTables = $request->totalTables;
                        if($newTotalTables > $oldTotalTables){
                            for($i = $oldTotalTables; $i < $newTotalTables; $i++){
                                $table = new Table();
                                $table->restaurant_id = $restaurant->id;
                                $table->table_number = $i+1;
                                $table->description = 'Normal';
                                $table->save();
                            }
                        }else{
                            for($i = $newTotalTables; $i<$oldTotalTables;$i++){
                                $table = Table::where('restaurant_id',$restaurant->id)
                                            ->where('table_number', $i+1);
                                $table->delete();
                            }
                        }
                        $restaurant->total_tables = $request->totalTables;
                    }
                    if( $request->has('tablesNextToWindow') ){
                        $tablesNextToWindow = explode(',',$request->tablesNextToWindow);
                        $tables = Table::where('restaurant_id', $restaurant->id)->get();
                        foreach ($tables as $table) {
                            if (in_array($table->table_number, $tablesNextToWindow)) {
                                $table->description = 'Next to window';
                                $table->save();
                            }
                        }
                    }

                    if( $request->has('totalRooms') ){
                        $oldTotalRooms = $restaurant->total_rooms;
                        $newTotalRooms = $request->totalRooms;
                        if($newTotalRooms > $oldTotalRooms){
                            for($i = $oldTotalRooms; $i < $newTotalRooms; $i++){
                                $table = new RestaurantRoom();
                                $table->restaurant_id = $restaurant->id;
                                $table->room_number = $i+1;
                                $table->save();
                            }
                        }else{
                            for($i = $newTotalRooms; $i<$oldTotalRooms;$i++){
                                $table = RestaurantRoom::where('restaurant_id',$restaurant->id)
                                            ->where('room_number', $i+1);
                                $table->delete();
                            }
                        }
                        $restaurant->total_rooms = $newTotalRooms;
                    }
                    if($request->has('manager')){
                        $restaurant->manager = $request->manager;
                        $manager = User::where('id',$request->manager)->first();
                        $manager->role_id = 3;
                        $manager->save();
                    }
                    $restaurant->save();
                    return response()->json(['success'=> 'success' ],200);
                }catch(\Exception $e) {
                    return response()->json(['error'=> $e->getMessage()], 400);
                }
            }
        } else {
            return response()->json(['error'=> 'auth'], 403);
        }
        

    }
    public function countOrdersToday(Request $request){
        $user_id = auth()->user()->id;
        $restaurantIds = Restaurant::where('user_id', $user_id)->pluck('id');

        $tableIds = Table::whereIn('restaurant_id', $restaurantIds)->pluck('id');
        $roomIds = RestaurantRoom::whereIn('restaurant_id', $restaurantIds)->pluck('id');

        $today = date('Y-m-d');
        

        $countOrders = Order::whereDate('order_time', $today)
            ->where(function($query) use ($tableIds, $roomIds) {
                $query->whereIn('table_id', $tableIds)
                    ->orWhereIn('room_id', $roomIds);
            })
            ->with('user')
            ->with('tables')
            ->with('rooms')
            ->with('restaurant')
            ->orderBy('order_time')
            ->count();
        return response()->json($countOrders);
    }

     public function destroy(Request $request,RestaurantImageController $restaurantImageController)
    {
        $validator = Validator::make($request->all(), [
            'restaurantId'=> 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['message'=> $validator->errors()],400);
        }
        $restaurant = Restaurant::where('id', $request->restaurantId)->first();
        if (!$restaurant) {
            return response()->json(['message'=> 'Not found the restaurant'],204);
        }else if( $restaurant->user_id != auth()->user()->id ) {
            return response()->json(['message'=> 'Authorization'],403);
        }else{
            try{
                $restaurantImages = RestaurantImage::where('restaurant_id', $restaurant->id)->get();
                    foreach ($restaurantImages as $image) {
                        $restaurantImageController->destroy($image->id);
                    }
                
                Storage::delete($restaurant->avatar);
                $restaurant->delete();
                return response()->json(true,200);
            }catch(\Exception $e){
                return response()->json(['message'=> $e->getMessage()],400);
            }
        }
    }
    public function getRestaurantsByUser()
    {
        try{
            $restaurants = Restaurant::where('user_id', auth()->user()->id)
                ->with('images')
                ->with('tables')
                ->with('rooms')
                ->with('manager_restaurant')
                ->orderByDesc('created_at')
                ->paginate(8);
            if ($restaurants->isEmpty()) {
                return response()->json(['message' => 'No restaurants found'], 204);
            }
            $restaurantsData = $restaurants->map(function ($restaurant) {
                $managerData = null;
                if ($restaurant->manager_restaurant) {
                    $managerData = [
                        'id' => $restaurant->manager_restaurant->id,
                        'email' => $restaurant->manager_restaurant->email,
                        'avatar' => Storage::url($restaurant->manager_restaurant->avatar),
                        'phone' => $restaurant->manager_restaurant->phone
                    ];
                }

                return [
                    'id' => $restaurant->id,
                    'name' => $restaurant->name,
                    'status'=> $restaurant->status,
                    'address' => $restaurant->address,
                    'phone' => $restaurant->phone,
                    'email' => $restaurant->email,
                    'description' => $restaurant->description,
                    'allow_booking' => (bool) $restaurant->allow_booking,
                    'date' => date('H:i d/m/Y', strtotime($restaurant->created_at)),
                    'avatar'=> Storage::url($restaurant->avatar),
                    'totalRooms' => $restaurant->total_rooms,
                    'images' => $restaurant->images->map(function ($image) {
                        return [
                            'id' => $image->id,
                            'image' => Storage::url($image->image)
                        ];
                    })->toArray(),
                    'tables' => $restaurant->tables->map(function ($table){
                        return[
                            'id'=> $table->id,
                            'tableNumber' => $table->table_number,
                            'description' => $table->description,
                        ];
                    })->toArray(),
                    'manager'=> $managerData
                ];
            });
            
            return response()->json([
                'items' => $restaurantsData,
                'current_page' => $restaurants->currentPage(),
                'per_page' => $restaurants->perPage(),
                'total' => $restaurants->total(),
                'last_page' => $restaurants->lastPage()
            ],200);
        }catch(\Exception $e) {
            return response()->json(['message'=> 'SERVER_ISSUE','errors'=>$e->getMessage()], 400);
        }
    }
    public function ownOrdersRestaurantsToday()
    {   
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $user_id = auth()->user()->id;
        $restaurantIds = Restaurant::where('user_id', $user_id)->pluck('id');

        $tableIds = Table::whereIn('restaurant_id', $restaurantIds)->pluck('id');
        $roomIds = RestaurantRoom::whereIn('restaurant_id', $restaurantIds)->pluck('id');

        $today = date('Y-m-d');
        

        $orders = Order::whereDate('order_time', $today)
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

    public function ownOrdersRestaurantsOld(){
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $user_id = auth()->user()->id;
        $restaurantIds = Restaurant::where('user_id', $user_id)->pluck('id');

        $tableIds = Table::whereIn('restaurant_id', $restaurantIds)->pluck('id');
        $roomIds = RestaurantRoom::whereIn('restaurant_id', $restaurantIds)->pluck('id');

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
            'today' => $today,
            'order_time' => $orders

            ],200);
    }
    public function ownOrdersRestaurantsFuture(Request $request){
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $user_id = auth()->user()->id;
        $restaurantIds = Restaurant::where('user_id', $user_id)->pluck('id');

        $tableIds = Table::whereIn('restaurant_id', $restaurantIds)->pluck('id');
        $roomIds = RestaurantRoom::whereIn('restaurant_id', $restaurantIds)->pluck('id');

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
            'today' => $today,
            'order_time' => $orders

            ],200);
    }
}
