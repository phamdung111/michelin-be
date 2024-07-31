<?php
 
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\TableOrderController;
 
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
    Route::post('/like', [FavoriteController::class, 'store'])->middleware('auth:api')->name('me');
    Route::post('/check', [FavoriteController::class, 'check'])->middleware('auth:api')->name('me');
    Route::post('/un-like', [FavoriteController::class, 'destroy'])->middleware('auth:api')->name('me');
    Route::post('/favorites', [FavoriteController::class, 'favoritesByUser'])->middleware('auth:api')->name('me');
    Route::post('/order', [OrderController::class, 'store'])->middleware('auth:api')->name('me');
    Route::post('/orders-user', [OrderController::class, 'orderByUser'])->middleware('auth:api')->name('me');
    Route::post('/user-cancel-order', [OrderController::class, 'cancelStatus'])->middleware('auth:api')->name('me');

    Route::post('/change-status', [OrderController::class, 'changeStatus'])->middleware('auth:api')->name('me');
});

Route::post('/restaurants', [RestaurantController::class, 'restaurants']);
Route::get('/restaurant/{id}', [RestaurantController::class, 'restaurant']);
Route::post('/tables-or-rooms-by-order-time', [TableOrderController::class, 'tables0rRoomsByOrderTime']);
