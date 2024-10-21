<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\JwtService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
class PusherController extends Controller
{
    public function auth(Request $request) {
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
        $userId = $jwtService->getUserFromToken($token,$loginSource);
        $user = User::findOrFail($userId);
        $request->setUserResolver(function () use ($user) {
            return $user;
        });
        return Broadcast::auth($request);
    }
}
