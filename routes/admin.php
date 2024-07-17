<?php
 
use App\Http\Controllers\Admin\AdminController;
use Illuminate\Support\Facades\Route;
 
Route::group([
    'middleware' => 'api',
    'prefix' => ''
], function ($router) {
    Route::post('/permission-restaurants', [AdminController::class, 'permissionRestaurants'])->middleware('auth:api')->name('me');
});

Route::get('/restaurants', [AdminController::class, 'restaurants'])->middleware('auth:api')->name('me');
