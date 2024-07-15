<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use App\Models\RestaurantImage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\RestaurantImageController;

class RestaurantController extends Controller
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
            $restaurant->save();

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
    public function getRestaurantByUser()
    {
        try{
            $restaurants = Restaurant::where('user_id', auth()->user()->id)
                ->with(['images'])
                ->orderByDesc('created_at')
                ->paginate(8);
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
                'last_page' => $restaurants->lastPage()
            ],200);
        }catch(\Exception $e) {
            return response()->json(['error'=> $e->getMessage()], 400);
        }
    }

    /**
     * Update the specified resource in storage.
     */
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
    
    public function restaurants(){
        $restaurants = Restaurant::where('status', 'approved')
                ->with(['images'])
                ->orderByDesc('created_at')
                ->limit(8)
                ->get();
        if ($restaurants->isEmpty()) {
            return response()->json(['message' => 'No restaurants'], 200);
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
        return response()->json($restaurantsData,200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Restaurant $restaurant)
    {
        //
    }
}
