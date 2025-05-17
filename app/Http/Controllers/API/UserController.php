<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required',
            'phone' => 'required',
            'password' => 'required',
            'profile_image' => 'required|image',
            'date_of_birth' => 'required',
        ]);

        $filepath = $request->profile_image->store('/profile_images', 'public');
        $profileimage = Storage::url($filepath);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'profile_image' => $profileimage,
            'date_of_birth' => $request->date_of_birth,
        ]);

        $token = $user->createToken('auth')->plainTextToken;

        return response()->json([
            'message' => 'User created successfully',
            'data' => $user,
            'token' => $token
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|exists:users,email',
            'password' => 'required',
        ]);

        if (Auth::attempt($request->only('email', 'password'))) {

            $user = User::where('email', $request->email)->first();

            $token = $user->createToken('auth')->plainTextToken;

            return response()->json([
                'message' => 'User Logged In',
                'data' => $user,
                'token' => $token
            ]);
        } else {
            return response()->json([
                'message' => 'Invalid Credentials',
            ], 400);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }
}
