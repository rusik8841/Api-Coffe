<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\AdminRequesr;
use App\Http\Requests\Api\User\RegisterRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request) {
        $user = User::where('login', $request->login)->first();

        if (!$user || $user->password !== $request->password) {
            return response()->json([
                'message' => 'Authentication failed'
            ], 401);
        }

        $token = $user->createToken('api-token')->plainTextToken;


        return response()->json([
            'user_token' => $token
        ], 200);
    }
    public function register(Request $request)
    {
        $role = Role::where('code', 'user')->first();

        $user = User::create([
            ...$request->validated(),
            'role_id' => $role->id,
        ]);
        // Создание токена
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'role' => $user->role->name,
            'token' => $token
        ], 201);
    }

    public function logout(Request $request) {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'data' => [
                'message' => 'logout'
            ]
        ], 200);
    }
}
