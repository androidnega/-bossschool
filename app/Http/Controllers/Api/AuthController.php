<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(LoginRequest $request, AuthService $auth): JsonResponse
    {
        $validated = $request->validated();

        $result = $auth->attemptLogin($validated['email'], $validated['password']);

        if ($result === null) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        return response()->json([
            'token' => $result['token'],
            'user' => $result['user'],
        ]);
    }

    public function logout(Request $request, AuthService $auth): JsonResponse
    {
        $auth->revokeCurrentToken($request->user());

        return response()->json(['message' => 'Logged out']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }
}
