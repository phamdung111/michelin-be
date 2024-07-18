<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FavoriteController extends Controller
{
    public function check(Request $request){
        $request->validate(['restaurantId'=>'required']);
        $favorite = Favorite::where('restaurant_id', $request->input('restaurantId'))
            ->where('user_id', auth()->user()->id)
            ->first();
        if($favorite){
            return response()->json(true,200);
        }else{
            return response()->json(false,200);
        }
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $restaurant = Restaurant::findOrFail($request->input('restaurantId'));
        if(!$restaurant) {
            return response()->json(['error'=> 'Not found the restaurant'], 400);
        }else{
            try {
            $favorite = new Favorite();
            $favorite->restaurant_id = $restaurant->id;
            $favorite->user_id = auth()->user()->id;
            $favorite->save();
            return response()->json(true,200);
            } catch (\Exception $e) {
                return response()->json(['error'=> $e->getMessage()],500);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $favorite = Favorite::where('restaurant_id', $request->input('restaurantId'))
            ->where('user_id', auth()->user()->id)
            ->first();
        if($favorite) {
            $favorite->delete();
        }
        return response()->json(false,200);
    }

    public function favoritesByUser(){
        $favorites = Favorite::where('user_id', auth()->user()->id)
            ->with('restaurant')
            ->with('restaurant.images')
            ->get();
        if($favorites) {
            $favoritesRestaurant = $favorites->map(function ($favorite) {
                return [
                    'id'=> $favorite->id,
                    'restaurant'=> [
                        'id' => $favorite->restaurant->id,
                        'name' => $favorite->restaurant->name,
                        'address' => $favorite->restaurant->address,
                        'description' => $favorite->restaurant->description,
                        'images'=> $favorite->restaurant->images->map(function ($image) {
                            return 
                                Storage::url($image->image);
                        }),
                    ]
                ];
            });
            return response()->json($favoritesRestaurant,200);
        } else {
            return response()->json(['status' => 'You have no favorites yet'], 200);
        }
    }
}
