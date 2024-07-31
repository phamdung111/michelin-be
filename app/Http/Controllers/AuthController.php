<?php
  
namespace App\Http\Controllers;
  
use App\Models\Role;
use Validator;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
  
  
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
            $user = new User;
            $user->name = request()->name;
            $user->email = request()->email;
            $user->password = bcrypt(request()->password);
            $user->avatar = '/user-placeholder.png';
            $user->role_id = '4';
            $user->save();

            $credentials = request(['email', 'password']);
            $token = auth()->attempt($credentials);
            return response()->json($this->respondWithToken($token), 201);
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
        $credentials = request(['email', 'password']);
  
        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
  
        return $this->respondWithToken($token);
    }
  
    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile()
    {
        return response()->json(
            [
                'id'=> auth()->user()->id,
                'name' => auth()->user()->name,
                'email'=> auth()->user()->email,
                'location'=> auth()->user()->location,
                'avatar'=> Storage::url(auth()->user()->avatar),
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
    public function logout()
    {
        auth()->logout();
  
        return response()->json(['message' => 'Successfully logged out']);
    }
  
    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
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
