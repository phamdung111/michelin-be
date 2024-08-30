<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\JwtService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
class AuthMiddleware
{
    protected $jwtService;

    public function __construct(JwtService $jwtService){
        $this->jwtService = $jwtService;
    }
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        $jwtService = new JwtService();
        if(!$token){
            return response()->json(['message'=>'Authenticated.'],401);
        }
        if($jwtService->validateToken($token)->status === false){
            if($jwtService->validateToken($token)->message === 'Token has expired'){
                return response()->json(['message'=>'Token has expired.'],401);
            }
            else{
                return response()->json(['message'=>'Authenticated.'],401);
            }
        }
        $userId = $this->jwtService->getUserFromToken($token);
        $user = User::find($userId);
        Auth::guard()->setUser($user);
        return $next($request);
    }
}
