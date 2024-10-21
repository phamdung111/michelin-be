<?php
namespace App\Services;

use App\Models\User;
use GuzzleHttp\Client;
use App\Models\PersonalAccessToken;
use Illuminate\Support\Facades\Http;

class JwtService
{
    function base64UrlEncode($data) {
        $base64 = base64_encode($data);
        $base64Url = rtrim(strtr($base64, '+/', '-_'), '=');
        return $base64Url;
    }

    function base64UrlDecode($data) {
        $base64 = strtr($data, '-_', '+/') . str_repeat('=', (4 - strlen($data) % 4) % 4);
        return base64_decode($base64);
    }

    private function generateToken($user_id, $exp,$login_source) {
        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT'
        ];
        $payload = [
            'id' => $user_id,
            'exp' => time() + $exp,
            'login_source' => $login_source
        ];
        $headerBase64 = $this->base64UrlEncode(json_encode($header));
        $payloadBase64 = $this->base64UrlEncode(json_encode($payload));

        $signatureInput = $headerBase64 . '.' . $payloadBase64;
        $signature = hash_hmac('sha256', $signatureInput, env('JWT_SECRET'), true);
        $signatureBase64 = $this->base64UrlEncode($signature);

        $token = $headerBase64 . '.' . $payloadBase64 . '.' . $signatureBase64;
        return $token;
    }
    public function generateJWTToken($user_id,$login_source){
        $access_token = $this->generateToken($user_id,3600,$login_source);
        $refresh_token = $this->generateToken($user_id,259200,$login_source);
        $personalAccessTOken = PersonalAccessToken::where('user_id',$user_id)->first();
        if(!$personalAccessTOken) {
            $personalAccessTOken = new PersonalAccessToken();
            $personalAccessTOken->user_id = $user_id;   
        }

        $personalAccessTOken->token = $access_token;
        $personalAccessTOken->refresh_token = $refresh_token;
        $personalAccessTOken->save();
        return (object) [
            'access_token' => $access_token,
            'refresh_token' => $refresh_token,
            'token_type' => 'bearer',
            'expires_in' => 3600,
            'login_source'=>'app'
        ];
    }
    public function validateToken($token,$loginSource){
        list($header, $payload, $signature) = explode('.',$token);
        $payloadData = json_decode(base64_decode($payload),true);
        $expToken = $payloadData['exp'];
        $user_id = $payloadData['id'];
        $personalAccessToken = PersonalAccessToken::where('user_id',$user_id)->first();
        $signatureInput = $header . '.' . $payload;
        $signatureApp = hash_hmac('sha256', $signatureInput, env('JWT_SECRET'), true);
        $signatureBase64 = $this->base64UrlEncode($signatureApp);
        if($expToken > time() && $signatureBase64 === $signature && ( $token === $personalAccessToken->token || $token === $personalAccessToken->refresh_token)){
            if($loginSource === 'app'){
                return (object) [
                    'status'=>true,
                    'message'=>'Token validated'
                ];
            }
            else{
                $user = User::findOrFail($user_id);
                if($loginSource === 'google'){
                    $google_access_token = $payloadData['access_token'];
                    $clientUser = new Client();
                    $userResponse = $clientUser->post('https://www.googleapis.com/oauth2/v3/userinfo',[
                        'headers' => [
                            'Authorization' => 'Bearer ' . $google_access_token,
                        ],
                    ]);
                    $body = $userResponse->getBody()->getContents();
                    $userData =  json_decode($body,true);
                    
                    if($userResponse->getStatusCode() === '401'){
                        return (object) [
                            'status'=>false,
                            'message'=>'Token has expired'
                        ];
                    }
                    elseif($userData['email'] === $user->email){
                        return (object) [
                            'status'=>true,
                            'message'=>'Token validated'
                        ];
                    }else{
                        return (object) [
                            'status'=>true,
                            'message'=>'Invalid token'
                        ];
                    }
                }
                elseif($loginSource === 'github'){
                    $userResponse = Http::withToken($token)->get('https://api.github.com/user');
                    $body = $userResponse->getBody()->getContents();
                    $userData =  json_decode($body,true);
                    if($userResponse->status() === '401'){
                        return (object) [
                            'status'=>false,
                            'message'=>'Token has expired'
                        ];
                    }
                    else{
                        return (object) [
                            'status'=>true,
                            'message'=>'Token validated'
                        ];
                    }
                }
            }
        }
            if($expToken < time()){
              return (object) [
                  'status'=>false,
                  'message'=>'Token has expired'
              ];
            }
        return (object) [
            'status'=>false,
            'message'=>'Invalid token'
        ];
    }
    public function getUserFromToken($token,$loginSource)
    {
        if (!$this->validateToken($token,$loginSource)->status) {
            return null;
        }
    
        list($header, $payload, $signature) = explode('.', $token);
        $payloadData = json_decode(base64_decode($payload), true);
        $userId = $payloadData['id'] ?? null;
        return $userId;
    }
    public function destroy(){
        $token = '';
        if(isset($_SERVER['HTTP_AUTHORIZATION'])){
            if (strpos($_SERVER['HTTP_AUTHORIZATION'], 'Bearer ') === 0) {
                $token = substr($_SERVER['HTTP_AUTHORIZATION'],7);
            }
        }
        list($header, $payload, $signature) = explode('.',$token);
        $payloadData = json_decode(base64_decode($payload),true);
        $userId = $payloadData['id'];
        try{
            PersonalAccessToken::where('user_id',$userId)->delete();
            return true;
        }catch(\Exception $e) {
            return response()->json(['error'->$e->message()],400);
        }
    }

    public function refreshToken(){
        $refreshToken = '';
        if(isset($_SERVER['HTTP_AUTHORIZATION'])){
            if (strpos($_SERVER['HTTP_AUTHORIZATION'], 'Bearer ') === 0) {
                $refreshToken = substr($_SERVER['HTTP_AUTHORIZATION'],7);
            }
        }
        $loginSource = getallheaders()['Login-Source'];
        $newToken = '';
        if($loginSource === 'app'){
            list($header, $payload, $signature) = explode('.',$refreshToken);

            $signatureInput = $header . '.' . $payload;
            $signatureApp = hash_hmac('sha256', $signatureInput, env('JWT_SECRET'), true);
            $signatureBase64 = $this->base64UrlEncode($signatureApp);

            $payloadData = json_decode(base64_decode($payload),true);
            if(!$this->validateToken($refreshToken,$loginSource)){
                return response()->json(['message' => 'Unauthenticated.'],401);
            }
            else{
                $header = [
                    'alg' => 'HS256',
                    'typ' => 'JWT'
                ];
                $payload = [
                    'id' => $payloadData['id'],
                    'exp' => time() + 3600,
                ];

                $headerBase64 = $this->base64UrlEncode(json_encode($header));
                $payloadBase64 = $this->base64UrlEncode(json_encode($payload));

                $signatureInput = $headerBase64 . '.' . $payloadBase64;
                $signature = hash_hmac('sha256', $signatureInput, env('JWT_SECRET'), true);
                $signatureBase64 = $this->base64UrlEncode($signature);

                $newToken = $headerBase64 . '.' . $payloadBase64 . '.' . $signatureBase64;
            }

            $userId = $payloadData['id'];
            $personalAccessToken = PersonalAccessToken::where('user_id',$userId)->first();
            
        }elseif($loginSource === 'google'){
            $personalAccessToken = PersonalAccessToken::where('refresh_token',$refreshToken)->first();
            $user = User::findOrFail('id',$personalAccessToken->user_id);
            if($loginSource === 'google'){
                $data = $this->googleRefreshToken($refreshToken,);
                $new_google_access_token = $data['access_token'];
                $expires_in = $data['expires_in'];
                $newToken = $this->generateTokenLoginOAuth($new_google_access_token,$user->id,$expires_in);
            }
        }
        $personalAccessToken->token = $newToken;
        $personalAccessToken->save();
        return $newToken;
    }
    public function generateTokenLoginOAuth($tokenOAuth,$userId,$expires_in){
        $header = [
                'alg' => 'HS256',
                'typ' => 'JWT'
            ];
        $payload = [
            'id' => $userId,
            'exp' => time() + $expires_in,
            'access_token' => $tokenOAuth,
        ];
        $headerBase64 = $this->base64UrlEncode(json_encode($header));
        $payloadBase64 = $this->base64UrlEncode(json_encode($payload));
        $signatureInput = $headerBase64 . '.' . $payloadBase64;
        $signature = hash_hmac('sha256', $signatureInput, env('JWT_SECRET'), true);
        $signatureBase64 = $this->base64UrlEncode($signature);
        $token = $headerBase64 . '.' . $payloadBase64 . '.' . $signatureBase64;
        return $token;
    }
    public function googleRefreshToken($refresh_token,) {
        try{
            $response = Http::post('https://oauth2.googleapis.com/token',[
                'client_id' => env('GOOGLE_CLIENT_ID'),
                'client_secret'=> env('GOOGLE_CLIENT_SECRET'),
                'refresh_token'=> $refresh_token,
                'grant_type'=> 'refresh_token'
            ]);
            $body = $response->getBody()->getContents();
            $data =  json_decode($body,true);
            return $data;
        }catch(\Exception $e){
            return response()->json(['errors' => $e->getMessage()],401);
        }
    }
}
