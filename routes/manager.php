<?php
 
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OwnRestaurantController;
use App\Http\Controllers\Manager\ManagerRestaurantController;
 
Route::group([
    'middleware' => 'api',
    'prefix' => ''
], function ($router) {
        Route::post('/count-orders-today', [ManagerRestaurantController::class, 'countOrdersToday'])->middleware('auth:api')->name('me');
        Route::post('/orders-restaurant-today', [ManagerRestaurantController::class, 'ordersRestaurantToday'])->middleware('auth:api')->name('me');
        Route::post('/orders-restaurant-old', [ManagerRestaurantController::class, 'ordersRestaurantOld'])->middleware('auth:api')->name('me');
        Route::post('/orders-restaurant-future', [ManagerRestaurantController::class, 'ordersRestaurantFuture'])->middleware('auth:api')->name('me');
});