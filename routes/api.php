<?php
 
use App\Http\Controllers\NotificationController;
use App\Services\JwtService;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Broadcast;
use App\Http\Controllers\GithubController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\PusherController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\TableOrderController;

Route::middleware(['custom-auth'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/profile', [AuthController::class, 'profile']);
    Route::post('/update-profile', [UserController::class, 'edit']);
    Route::post('/new-restaurant', [RestaurantController::class, 'store']);
    Route::post('/refresh-token', [JwtService::class, 'refreshToken']);
    Route::post('/profile', [AuthController::class, 'profile']);
    Route::post('/update-avatar', [UserController::class, 'updateAvatar']);
    Route::post('/like', [FavoriteController::class, 'store']);
    Route::post('/check', [FavoriteController::class, 'check']);
    Route::post('/un-like', [FavoriteController::class, 'destroy']);
    Route::post('/favorites', [FavoriteController::class, 'favoritesByUser']);
    Route::post('/order', [OrderController::class, 'store']);
    Route::post('/orders-user', [OrderController::class, 'orderByUser']);
    Route::post('/user-cancel-order', [OrderController::class, 'cancelStatus']);
    Route::post('/change-status', [OrderController::class, 'changeStatus']);
    Route::post('/comment', action: [CommentController::class, 'store']);
    Route::post('/delete-comment', [CommentController::class, 'destroy']);
    Route::post('/edit-comment', [CommentController::class, 'edit']);
    Route::post('/notifications', [NotificationController::class, 'index']);
    Route::post('/count-notifications-unread', [NotificationController::class, 'countUnread']);

});


Route::post('/restaurants', [RestaurantController::class, 'restaurants']);
Route::get('/restaurant/{id}', [RestaurantController::class, 'restaurant']);
Route::post('/tables-or-rooms-by-order-time', [TableOrderController::class, 'tables0rRoomsByOrderTime']);

Route::get('/comments', [CommentController::class, 'show']);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');

//oauth callback
Route::post('/github/callback', [GitHubController::class, 'handleProviderCallback']);
Route::post('/google/callback', [GoogleController::class, 'googleAccountCallback']);

//pusher endpoint
Route::post('/pusher/auth',[PusherController::class,'auth']);