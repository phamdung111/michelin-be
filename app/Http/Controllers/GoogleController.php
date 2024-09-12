<?php

namespace App\Http\Controllers;

use App\Models\PersonalAccessToken;
use App\Services\JwtService;
use stdClass;
use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GoogleController extends Controller
{
    public function googleAccountCallback(Request $request)
    {
        try{
            $code = $request->input('code');
            $client = new Client();
            $response = $client->post('https://oauth2.googleapis.com/token', [
                'form_params' => [
                    'code' => $code,
                    'client_id' => env('GOOGLE_CLIENT_ID'),
                    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
                    'redirect_uri' => env('GOOGLE_CLIENT_REDIRECT'),
                    'grant_type' => 'authorization_code',
                ]
            ]);
            $data = json_decode($response->getBody(), true);
            $access_token_google = $data['access_token'];
            $expires_in = $data['expires_in'];
            $refresh_token =$data['refresh_token'];

            $clientUser = new Client();
            $userResponse = $clientUser->post('https://www.googleapis.com/oauth2/v3/userinfo',[
                'headers' => [
                    'Authorization' => 'Bearer ' . $access_token_google,
                ],
            ]);

            $body = $userResponse->getBody()->getContents();
            $userData =  json_decode($body,true);
            $user = User::where('email',$userData['email'])->first();
            if($user && $user->login_resource !== 'google'){
                return response()->json([
                    'message'=> 'User exist in app',
                    'login_source' => $user->login_resource,
                ],200);
            }
            elseif(!$user){
                $user = new User();
                $user->role_id = '4';
            }
            $user->email = $userData['email'];
            $user->name = $userData['name'];
            $user->avatar = $userData['picture'];
            $user->login_resource = 'google';
            $user->save(); 
            $personalAccessToken = PersonalAccessToken::where('user_id',$user->id)->first();
            if(!$personalAccessToken){
                $personalAccessToken = new PersonalAccessToken();
                $personalAccessToken->user_id = $user->id;
            }
            $jwtService = new JwtService();
            $access_token = $jwtService->generateTokenLoginOAuth($access_token_google,$user->id,$expires_in);
            $personalAccessToken->token = $access_token;
            $personalAccessToken->refresh_token = $refresh_token;
            $personalAccessToken->save();
            return response()->json([
                'access_token'=> $access_token,
                'expires_in' => $expires_in,
                'refresh_token' => $refresh_token,
                'token_type' => 'Bearer',
                'login_source'=> 'google'
            ]);
        }catch(\Exception $e){
            return response()->json([$e->getMessage()],400);
        }
    }
    function base64UrlEncode($data) {
        $base64 = base64_encode($data);
        $base64Url = rtrim(strtr($base64, '+/', '-_'), '=');
        return $base64Url;
    }

    function base64UrlDecode($data) {
        $base64 = strtr($data, '-_', '+/') . str_repeat('=', (4 - strlen($data) % 4) % 4);
        return base64_decode($base64);
    }
}
