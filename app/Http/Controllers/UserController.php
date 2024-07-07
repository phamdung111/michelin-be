<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
            if($currentPassword) {
                if (!Hash::check($currentPassword, $user->password)) {
                    return response()->json(['error' => 'current password not match'], 400);
                }
                else {
                    if ($newPassword !== $repeatPassword) {
                        return response()->json(['error' => 'password not match'], 400);
                    }
                    else {
                        $user->password = Hash::make($newPassword);
                    }
                }
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
