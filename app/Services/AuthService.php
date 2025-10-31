<?php

namespace App\Services;

use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    public function register (array $request )
    {
        $user = User::create($request);
        $token = JWTAuth::fromUser($user);
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => auth('api')->user(),
        ];
    }

    public function login(array $request)
    {
        $token = auth('api')->attempt($request);
        if (! $token = JWTAuth::attempt($request)) {
            return response()->json(['error' => 'Identifiants invalides']);
        }
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => auth('api')->user(),
        ];
    }

    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'Déconnecté avec succès']);
    }

    public function getAuthenticatedUser()
    {
        return response()->json(auth()->user());
    }
}
