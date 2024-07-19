<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\Restaurant;
use App\Policies\OrderPolicy;
use App\Policies\RestaurantPolicy;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Restaurant::class, RestaurantPolicy::class);
        Gate::policy(RestaurantPolicy::class, RestaurantPolicy::class);
        Gate::policy(Order::class, OrderPolicy::class);
    }
}
