<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request)
    {
        $request->validate(['name'=> 'required']);
        $request->validate(['email'=> 'required']);
        $currentPassword = $request->input('currentPassword');
        $newPassword = $request->input('newPassword');
        $repeatPassword = $request->input('repeatPassword');
        
        try {
            $user = User::find(auth()->user()->id);
            $errors = [];
            if($currentPassword) {
                if (!Hash::check($currentPassword, $user->password)) {
                    $errors['currentPassword'] = 'current password not match';
                }
                if(strlen($newPassword) < 8){ 
                    $errors['newPassword'] = 'password at least 8 characters';
                }
                if ($newPassword !== $repeatPassword || $repeatPassword === '') {
                    $errors['repeatPassword'] = 'password not match';
                }
                if(Hash::check($newPassword, $user->password)){
                    $errors['newPassword'] = 'new password matches the current password';
                }
            }
            if(!empty($errors)){
                return response()->json(['errors'=>$errors],400);
                }
            else{
                $user->password = bcrypt($newPassword);
            }
            $user->name = $request->input('name');
            $user->email = $request->input('email');
            $user->description = $request->input('description');
            $user->phone = $request->input('phone');

            $user->save();
            return response()->json(['status'=> 'success'],200);
        }catch (\Exception $e) {
            return response()->json(['error'=> $e->getMessage()], 400);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        
    }

    public function updateAvatar(Request $request)
    {
        $request->validate(['image' => 'required|mimes:png,jpg,jpeg']);
        $user = User::find(auth()->user()->id);
         try{
            $image = $request->file('image');
            $oldAvatar = $user->avatar;
            $name = $image->hashName();
            Storage::putFileAs('avatars', $image, $name);
            $user->avatar = '/avatars/'. $name;
            $user->save();
            Storage::delete($oldAvatar);
            return response()->json(['status'=>'success'],200);
        }catch(\Exception $e) {
            return response()->json(['error'=>$e->getMessage()],400);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
