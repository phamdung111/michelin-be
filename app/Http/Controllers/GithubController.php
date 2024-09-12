<?php

namespace App\Http\Controllers;

use App\Models\PersonalAccessToken;
use App\Models\User;
use App\Services\JwtService;
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

            //save info
            $user = User::where('id',$userResponse->json()['id'])->first();
            if(!$user){
                $user = new User();
                $user->name = $userResponse->json()['login'];
                $user->login_resource = 'github';
                $user->role_id = '4';
            }
            $user->avatar = $userResponse->json()['avatar_url'];
            $user->email = $userResponse->json()['email'];
            $user->save();
            $personalAccessToken = new PersonalAccessToken();
            $jwtService = new JwtService();
            $githubAppToken = $jwtService->generateTokenLoginOAuth($objectResponse->access_token,$user->id,$objectResponse->expires_in);
            $personalAccessToken->token = $githubAppToken;
            $personalAccessToken->refresh_token = $objectResponse->refresh_token;
            $personalAccessToken->user_id = $user->id;
            $personalAccessToken->save();
            return response()->json([
                'access_token'=>$githubAppToken,
                'expires_in'=>$objectResponse->expires_in,
                'token_type'=>$objectResponse->token_type,
                'refresh_token'=>$objectResponse->refresh_token,
                'login_source'=>'github',
            ],200);
        }
        catch(\Exception $e){
            return response()->json(['errors'=>$e->getMessage()]);
        }
    }
}
