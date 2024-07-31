<?php
 
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\OwnRestaurantController;
 
Route::group([
    'middleware' => 'api',
    'prefix' => ''
], function ($router) {
        Route::post('/count-orders-today', [OrderController::class, 'countOrdersToday'])->middleware('auth:api')->name('me');
        Route::post('/check-manager', [OwnRestaurantController::class, 'checkManager'])->middleware('auth:api')->name('me');
        Route::post('/update-restaurant', [OwnRestaurantController::class, 'update'])->middleware('auth:api')->name('me');
});


