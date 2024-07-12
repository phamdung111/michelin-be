<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RestaurantImage;
use Illuminate\Support\Facades\Storage;

class RestaurantImageController extends Controller
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
    public function store($file, $restaurant_id)
    {   
        $restaurant_image = new RestaurantImage();
        $name = $file->hashName();
        Storage::putFileAs("images", $file, $name);
        $restaurant_image->restaurant_id = $restaurant_id;
        $restaurant_image->image = 'images/'. $name;
        $restaurant_image->save();
    }

    /**
     * Display the specified resource.
     */
    public function show(RestaurantImage $restaurantImage)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RestaurantImage $restaurantImage)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RestaurantImage $restaurantImage)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($imageId)
    {
        $image = RestaurantImage::findOrFail($imageId);
        Storage::delete($image->image);
        if (!is_null($image->image) && file_exists(public_path() . $image->image)) {
                unlink(public_path() . $image->image);
            }
        Storage::deleteDirectory($image);
        $image->delete();
    }
}
