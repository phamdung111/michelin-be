<?php
 
use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
 
Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api')->name('logout');
    Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('auth:api')->name('refresh');
    Route::post('/profile', [AuthController::class, 'profile'])->middleware('auth:api')->name('me');
    Route::post('/update-profile', [UserController::class, 'edit'])->middleware('auth:api')->name('me');
    Route::post('/update-avatar', [UserController::class, 'updateAvatar'])->middleware('auth:api')->name('me');
    Route::post('/new-restaurant', [RestaurantController::class, 'store'])->middleware('auth:api')->name('me');
    Route::post('/restaurant-by-user', [RestaurantController::class, 'getRestaurantByUser'])->middleware('auth:api')->name('me');
    Route::post('/update-restaurant', [RestaurantController::class, 'update'])->middleware('auth:api')->name('me');
});
