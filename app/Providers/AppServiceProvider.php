<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\Restaurant;
use App\Services\JwtService;
use App\Policies\OrderPolicy;
use App\Policies\RestaurantPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        $this->app->singleton(JwtService::class, function ($app) {
            return new JwtService();
        });
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
