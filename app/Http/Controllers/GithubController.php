<?php

namespace App\Http\Controllers;

use stdClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;

class GithubController extends Controller
{

    public function redirectToProvider()
    {
        return Socialite::driver('github')->redirect();
    }
    public function handleProviderCallback(Request $request)
    {
        try{
            $code = $request->input('code');
            $response = Http::asForm()->post('https://github.com/login/oauth/access_token', [  
                'client_id' => env('GITHUB_CLIENT_ID'),  
                'client_secret' => env('GITHUB_CLIENT_SECRET'),  
                'code' => $code,
            ]);
            $objectResponse = new stdClass();
            foreach( explode('&', $response->body()) as $attr){
                list($key, $value) = explode('=', $attr);
                $objectResponse->$key = $value;
            };
            $token = $objectResponse->access_token;
            $userResponse = Http::withToken($token)->get('https://api.github.com/user');

            //save token
            // $user = 

            return response()->json([
                'access_token'=>$objectResponse->access_token,
                'expires_in'=>$objectResponse->expires_in,
                'token_type'=>$objectResponse->token_type,
                'refresh_token'=>$objectResponse->refresh_token,
                'refresh_token_expires_in'=>$objectResponse->refresh_token_expires_in,
                'login_source'=>'github',
                'user' => $userResponse->body()
            ],200);
        }
        catch(\Exception $e){
            return response()->json(['errors'=>$e->getMessage()]);
        }
    }
}
