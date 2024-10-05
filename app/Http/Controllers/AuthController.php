<?php
  
namespace App\Http\Controllers;
  
use App\Services\JwtService;
use Illuminate\Http\Request;
use Validator;
use App\Models\Role;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
class AuthController extends Controller
{
 
    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register() {
        $validator = Validator::make(request()->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:8',
        ]);
  
        if($validator->fails()){
            return response()->json(['errors'=> $validator->errors()],400);
        }
        try {
            $email = request()->email;
            $password = password_hash(request()->password, PASSWORD_BCRYPT);
            $user = new User;
            $user->name = request()->name;
            $user->email = $email;
            $user->password = $password;
            $user->avatar = '/user-placeholder.png';
            $user->role_id = '4';
            $user->login_source = 'app';
            $user->save();
            
            $jwt = new JwtService();

            $token = $jwt->generateJWTToken($user->id,$user->login_source);

            return response()->json([$token],201);
        }catch(\Exception $e){
            return response()->json($e->getMessage(), 400);
        }
    }
  
  
    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()  
    {   
        $validator = Validator::make(request()->all(),[  
            'email' => 'required|email',  
            'password' => 'required',  
        ]);  

        if ($validator->fails()) {  
            return response()->json(['errors' => $validator->errors()], 422);  
        }  

        $email = request()->email;  
        $password = request()->password;

        $user = User::where('email', $email)->first();  

        if (!$user || !password_verify($password, $user->password)) {  
            return response()->json(['message' => 'Unauthorized.',
        ], 401);
        }  

        $jwt = new JwtService();  
        $token = $jwt->generateJWTToken($user->id,$user->login_source);
        return response()->json($token);  
    }
    function base64UrlDecode($data) {
        $base64 = strtr($data, '-_', '+/') . str_repeat('=', (4 - strlen($data) % 4) % 4);
        return base64_decode($base64);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile(Request $request)
    {
        $avatar = '';
        !str_starts_with(auth()->user()->avatar,'https') ? $avatar = Storage::url(auth()->user()->avatar) : $avatar = auth()->user()->avatar;
        return response()->json(
            [
                'id'=> auth()->user()->id,
                'name' => auth()->user()->name,
                'email'=> auth()->user()->email,
                'location'=> auth()->user()->location,
                'avatar'=> $avatar,
                'role' => Role::where('id', auth()->user()->role_id)->value('name'),
                'phone'=> auth()->user()->phone,
                'description'=> auth()->user()->description,
            ]
        );
    }
  
    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $jwtService = new JwtService();
        $jwtService->destroy();
        return response()->json(['message' => 'Successfully logged out']);
    }
  
    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        $jwtService = new JwtService();
        $newToken = $jwtService->refreshToken();
        return $newToken;
    }
  
    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
    
}
