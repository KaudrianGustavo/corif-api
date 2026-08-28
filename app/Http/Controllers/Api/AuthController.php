<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller{

    public function login(Request $request) {

        $request->validate([
            'email'     =>'required|email',
            'password'=> 'required'
        ]);

        $email = $request->email;
        $password = $request->password;

        if (! Auth::attempt(['email' => $email, 'password' => $password])) {
            return response()->json([
                'message' => 'Credenciais inválidas'
            ], 401);
        } 

        $user =  Auth::user();
        $token = $user->createToken('token-login')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => $user
        ]);
    }

    public function logout(Request $request) {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message'=> 'loggout realizado com sucesso'
        ]);

    }
}
