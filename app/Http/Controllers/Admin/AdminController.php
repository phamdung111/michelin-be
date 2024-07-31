<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    public function restaurants(Request $request){
        $page = $request->get('page');
        try{
            $restaurants = Restaurant::
                with(['images'])
                ->orderByDesc('created_at')
                ->paginate(8, ['*'], 'page',(int) $page);
            if ($restaurants->isEmpty()) {
                return response()->json(['message' => 'No restaurants found'], 200);
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
                'last_page' => $restaurants->lastPage(),
            ],200);
        }catch(\Exception $e) {
            return response()->json(['error'=> $e->getMessage()], 400);
        }
    }
    public function permissionRestaurants(Request $request){
        $data = $request->all();
        if(auth()->user()->id === 1){
            try{
                foreach ($data as $restaurant_id => $restaurant_status) {
                    $restaurant = Restaurant::find($restaurant_id);
                    $restaurant->status = $restaurant_status;
                    $restaurant->save();
                    $user = User::where('id',$restaurant->user_id)->first();
                    $user->role_id = 2;
                    $user->save();
                }
                return response()->json(['status'=> 'success'],200);
            }catch(\Exception $e) {
                return response()->json(['error'=> $e->getMessage()], 400);
            }
        }else{
            return response()->json(['error'=> 'permission'], 400);
        }
        
    }
}
