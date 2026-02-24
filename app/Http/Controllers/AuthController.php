<?php

namespace App\Http\Controllers;


use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request): \Illuminate\Http\Response|\Illuminate\Contracts\Routing\ResponseFactory
    {

        $fields = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string'
        ]);

        $user = User::where ('email', $fields['email'])->first();
        if (!$user || !Hash::check($fields['password'], $user->password)) {
            return response(['message' => 'Invalid Credentials'], 401);
        }
        $token = $user->createToken('my-app-token')->plainTextToken;
        $response = ['token' => $token, 'user' => $user];

        return response($response, 200);
    }

    public function logout(Request $request) {
        auth()->user()->tokens()->delete();
        return response(['message' => 'Logged out']);
    }
}
