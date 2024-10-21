<?php

namespace App\Http\Controllers;

use App\Events\MyEvent;
use App\Models\User;
use App\Models\Table;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use App\Models\RestaurantRoom;
use App\Models\RestaurantImage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\RestaurantImageController;

class RestaurantController extends Controller
{

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, RestaurantImageController $restaurantImageController)
    {

        $validator = Validator::make($request->all(),[
            'name'=> 'required',
            'email' => [
                'required',
                'email',
                Rule::unique('restaurants', 'email')
                    ->whereNot('user_id', auth()->user()->id)
            ],
            'address'=> 'required | unique:restaurants,address',
            'phone' => [
                'required',
                Rule::unique('restaurants', 'phone')
                    ->whereNot('user_id', auth()->user()->id)
            ],
            'description'=> 'required',
            'image0'=> 'required|image',
            'allow_booking'=> 'required',
            'avatar'=> 'required|image',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        try {
            $restaurant = new Restaurant();
            $restaurant->user_id = auth()->user()->id; 
            $restaurant->name = $request->name;
            $restaurant->address = $request->address;
            $restaurant->phone = $request->phone;
            $restaurant->email = $request->email;
            $restaurant->description = $request->description;
            $restaurant->allow_booking = $request->allow_booking == 'true' ? true : false;
            $restaurant->status = 'pending';
            $avatar = $request->avatar;
            $nameAvatar = $avatar->hashName();
            Storage::putFileAs('images', $avatar, $nameAvatar);
            $restaurant->avatar = '/images/'. $nameAvatar;
            $totalTables = $request->totalTables;
            $restaurant->total_tables = $totalTables;
            $totalRooms = $request->totalRooms;
            $restaurant->total_rooms = $totalRooms;
            $restaurant->save();

            $user = User::where('id',auth()->user()->id)->first();
            $user->role_id = 2;
            $user->save();
            
            if($totalTables > 0){
                $tablesNextToWindow =explode(',',$request->tablesNextToWindow);
                for($i = 1; $i <= $totalTables; $i++){
                    $table = new Table();
                    $table->restaurant_id = $restaurant->id;
                    if(in_array($i, $tablesNextToWindow)){
                        $table->table_number = $i;
                        $table->description = 'Next to window';
                    }else{
                        $table->table_number = $i;
                        $table->description = 'Normal';
                    }
                    $table->save();
                }
            }
            if($totalRooms > 0){
                for($i = 1; $i <= $totalRooms; $i++){
                    $room = new RestaurantRoom();
                    $room->restaurant_id = $restaurant->id;
                    $room->room_number = $i;
                    $room->save();
                }
            }
            for( $i = 0; $i < 4; $i++ ) {
                ${"image$i"} = $request->{"image$i"};
                if(${"image$i"} ){
                    $restaurantImageController->store(${"image$i"}, $restaurant->id);
                }
            }

            return response()->json(['status'=> 'success'],200);
        }
        catch (\Exception $e) {
            return response()->json(['error'=> $e->getMessage()], 400);
        }
    }

    /**
     * Display the specified resource.
     */
    

    /**
     * Update the specified resource in storage.
     */
    
    public function restaurants(Request $request){
        $perPage = $request->perPage;
        $restaurants = Restaurant::where('status', 'approved')
                ->with(['images'])
                ->orderByDesc('created_at')
                ->paginate($perPage);
        if ($restaurants->isEmpty()) {
            return response()->json(['message' => 'No data'], 204);
        }

        $restaurantsData = $restaurants->map(function ($restaurant) {
            return [
                'id'=> $restaurant->id,
                'name' => $restaurant->name,
                'status'=> $restaurant->status,
                'address' => $restaurant->address,
                'phone'=> $restaurant->phone,
                'email' => $restaurant->email,
                'description'=> $restaurant->description,
                'allow_booking'=>(bool) $restaurant->allow_booking,
                'date' => date('H:i d/m/Y', strtotime($restaurant->created_at)),
                'avatar'=> Storage::url($restaurant->avatar),
                'countLike' => $restaurant->count_like,
                'images' => $restaurant->images->map(function ($image) {
                    return [
                        'id' => $image->id,
                        'image' => Storage::url($image->image)
                    ];
                })->toArray()
            ];
        });
        return response()->json([
            'items' => $restaurantsData,
            'current_page' => $restaurants->currentPage(),
            'per_page' => $restaurants->perPage(),
            'total' => $restaurants->total(),
            'last_page' => $restaurants->lastPage()
        ],200);
    }

    public function restaurant($id){
        $restaurant = Restaurant::where('id', $id,)
                ->where('status', 'approved')
                ->with(['images','tables','rooms','comments','comments.user'])
                ->first();
        if (!$restaurant) {
            return response()->json(['message' => 'No restaurant'], 204);
        }

        return response()->json([
            'id'=> $restaurant->id,
            'name' => $restaurant->name,
            'status'=> $restaurant->status,
            'address' => $restaurant->address,
            'phone'=> $restaurant->phone,
            'email' => $restaurant->email,
            'description'=> $restaurant->description,
            'allow_booking'=>(bool) $restaurant->allow_booking,
            'countLike' => $restaurant->count_like,
            'tables' => $restaurant->tables->map(function ($table){
                return [
                    'id'=>$table->id,
                    'tableNumber' =>$table->table_number,
                    'description' =>$table->description,
                ];
            }),
            'date' => date('H:i d/m/Y', strtotime($restaurant->created_at)),
            'avatar'=> Storage::url($restaurant->avatar),
            'images' => $restaurant->images->map(function ($image) {
                return [
                    'id' => $image->id,
                    'image' => Storage::url($image->image)
                ];
            })->toArray(),
            'rooms'=> $restaurant->rooms->map(function ($room){
                return [
                    'id' => $room->id,
                    'roomNumber' => $room->room_number
                ];
            }),
            'comments'=>$restaurant->comments->map(function ($comment){
                return [
                    'id'=>$comment->id,
                    'content' => $comment->content
                ];
            })
        ],200);
    }
}
