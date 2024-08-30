<?php
 
use App\Http\Middleware\AuthMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
 
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__.'/../routes/console.php',
        using: function () {
        Route::middleware('api')
            ->prefix('api')
            ->group(base_path('routes/api.php'));
        
        Route::middleware('api')
            ->prefix('admin')
            ->group(base_path('routes/admin.php'));
        Route::middleware('api')
            ->prefix('manager')
            ->group(base_path('routes/manager.php'));
        Route::middleware('api')
            ->prefix('own-restaurant')
            ->group(base_path('routes/own-restaurant.php'));
        Route::middleware('web')
            ->group(base_path('routes/web.php'));
    },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->prependToGroup('custom-auth', [
        AuthMiddleware::class,
    ]);

    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => $e->getMessage(),
                ], 401);
            }
        });
    })->create();