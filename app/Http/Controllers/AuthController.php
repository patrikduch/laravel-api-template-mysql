<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Response;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.',
                'errors' => [
                    'email' => ['The provided credentials are incorrect.']
                ]
            ], Response::HTTP_UNAUTHORIZED); // 401
        }

        $user  = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type'   => 'Bearer',
        ], Response::HTTP_OK);
    }

    // POST /auth/logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out',
        ], Response::HTTP_OK);
    }

    // GET /auth/me
    public function me(Request $request)
    {
        return response()->json($request->user(), Response::HTTP_OK);
    }
}
