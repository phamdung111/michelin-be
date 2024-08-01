<?php
 
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Own\OwnRestaurantController;
 
Route::group([
    'middleware' => 'api',
    'prefix' => ''
], function ($router) {
        Route::post('/count-orders-today', [OwnRestaurantController::class, 'countOrdersToday'])->middleware('auth:api')->name('me');
        Route::post('/check-manager', [OwnRestaurantController::class, 'checkManager'])->middleware('auth:api')->name('me');
        Route::post('/update-restaurant', [OwnRestaurantController::class, 'update'])->middleware('auth:api')->name('me');
        Route::post('/delete-restaurant', [OwnRestaurantController::class, 'destroy'])->middleware('auth:api')->name('me');
        Route::post('/restaurants-by-own', [OwnRestaurantController::class, 'getRestaurantsByUser'])->middleware('auth:api')->name('me');
        Route::post('/own-orders-restaurants-today', [OwnRestaurantController::class, 'ownOrdersRestaurantsToday'])->middleware('auth:api')->name('me');
        Route::post('/old-order-restaurant', [OwnRestaurantController::class, 'ownOrdersRestaurantsOld'])->middleware('auth:api')->name('me');
        Route::post('/future-order-restaurant', [OwnRestaurantController::class, 'ownOrdersRestaurantsFuture'])->middleware('auth:api')->name('me');
});


