<?php
namespace App\Services;

use App\Models\PersonalAccessToken;
use App\Models\User;

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

    private function generateToken($user_id, $exp,$login_resource) {
        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT'
        ];
        $payload = [
            'id' => $user_id,
            'exp' => time() + $exp,
            'login_resource' => $login_resource
        ];
        $headerBase64 = $this->base64UrlEncode(json_encode($header));
        $payloadBase64 = $this->base64UrlEncode(json_encode($payload));

        $signatureInput = $headerBase64 . '.' . $payloadBase64;
        $signature = hash_hmac('sha256', $signatureInput, env('JWT_SECRET'), true);
        $signatureBase64 = $this->base64UrlEncode($signature);

        $token = $headerBase64 . '.' . $payloadBase64 . '.' . $signatureBase64;
        return $token;
    }
    public function generateJWTToken($user_id,$login_resource){
        $access_token = $this->generateToken($user_id,10,$login_resource);
        $refresh_token = $this->generateToken($user_id,259200,$login_resource);
        $personalAccessTOken = PersonalAccessToken::where('user_id',$user_id)->first();
        if($personalAccessTOken) {
            $personalAccessTOken->token = $access_token;
            $personalAccessTOken->refresh_token = $refresh_token;
            $personalAccessTOken->save();
        }else{
            $personalAccessTOken = new PersonalAccessToken();
            $personalAccessTOken->user_id = $user_id;
            $personalAccessTOken->token = $access_token;
            $personalAccessTOken->refresh_token = $refresh_token;
            $personalAccessTOken->save();
        }
        $personalAccessTOken->user_id = $user_id;
        $personalAccessTOken->token = $access_token;
        $personalAccessTOken->refresh_token = $refresh_token;
        $personalAccessTOken->save();
        return (object) [
            'access_token' => $access_token,
            'refresh_token' => $refresh_token,
            'token_type' => 'bearer',
            'expires_in' => 3600,
            'refresh_token_expires_in' => 259200,
        ];
    }

    public function refreshToken(){
        $refreshToken = '';
        if(isset($_SERVER['HTTP_AUTHORIZATION'])){
            if (strpos($_SERVER['HTTP_AUTHORIZATION'], 'Bearer ') === 0) {
                $refreshToken = substr($_SERVER['HTTP_AUTHORIZATION'],7);
            }
        }
        list($header, $payload, $signature) = explode('.',$refreshToken);

        $signatureInput = $header . '.' . $payload;
        $signatureApp = hash_hmac('sha256', $signatureInput, env('JWT_SECRET'), true);
        $signatureBase64 = $this->base64UrlEncode($signatureApp);

        $payloadData = json_decode(base64_decode($payload),true);
        $expRefreshToken = $payloadData['exp'];
        
        $newToken = '';

        if(!$this->validateToken($refreshToken)){
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
        $personalAccessToken->token = $newToken;
        $personalAccessToken->save();
        return $newToken;
    }
    public function validateToken($token){
        list($header, $payload, $signature) = explode('.',$token);

        $payloadData = json_decode(base64_decode($payload),true);
        $expToken = $payloadData['exp'];
        $user_id = $payloadData['id'];
        $personalAccessToken = PersonalAccessToken::where('user_id',$user_id)->first();
        $signatureInput = $header . '.' . $payload;
        $signatureApp = hash_hmac('sha256', $signatureInput, env('JWT_SECRET'), true);
        $signatureBase64 = $this->base64UrlEncode($signatureApp);
        if($expToken > time() && $signatureBase64 === $signature && ($token === $personalAccessToken->token || $token === $personalAccessToken->refresh_token)){
          return (object) [
              'status'=>true,
              'message'=>'Token validated'
          ];

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
    public function getUserFromToken($token)
    {
        if (!$this->validateToken($token)->status) {
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
}
