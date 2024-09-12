<?php
 
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Own\OwnRestaurantController;
 
Route::middleware(['custom-auth'])->group(function () {
    Route::post('/restaurants-by-own', [OwnRestaurantController::class, 'getRestaurantsByUser']);
    Route::post('/count-orders-today', [OwnRestaurantController::class, 'countOrdersToday']);
    Route::post('/check-manager', [OwnRestaurantController::class, 'checkManager']);
    Route::post('/update-restaurant', [OwnRestaurantController::class, 'update']);
    Route::post('/delete-restaurant', [OwnRestaurantController::class, 'destroy']);
    Route::post('/restaurants-by-own', [OwnRestaurantController::class, 'getRestaurantsByUser']);
    Route::post('/own-orders-restaurants-today', [OwnRestaurantController::class, 'ownOrdersRestaurantsToday']);
    Route::post('/old-order-restaurant', [OwnRestaurantController::class, 'ownOrdersRestaurantsOld']);
    Route::post('/future-order-restaurant', [OwnRestaurantController::class, 'ownOrdersRestaurantsFuture']);
});
