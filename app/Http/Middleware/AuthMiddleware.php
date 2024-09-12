<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use GuzzleHttp\Client;
use App\Services\JwtService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

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
        $loginSource = getallheaders()['Login-Source'];
        $jwtService = new JwtService();
        if(!$token){
            return response()->json(['message'=>'Authenticated.'],401);
        }
        if($jwtService->validateToken($token,$loginSource)->status === false){
            if($jwtService->validateToken($token,$loginSource)->message === 'Token has expired'){
                return response()->json(['message'=>'Token has expired.'],401);
            }
            else{
                return response()->json(['message'=>'Authenticated..'],401);
            }
        }
        $userId = $this->jwtService->getUserFromToken($token,$loginSource);
        $user = User::find($userId);
        Auth::guard()->setUser($user);
        return $next($request);
    }
}
