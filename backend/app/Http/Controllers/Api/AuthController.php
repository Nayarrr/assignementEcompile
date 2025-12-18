<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        return $this->apiResponse([
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
        ], 'Registration successful', 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated());

        if (!$result) {
            return $this->apiResponse([
                'errors' => [
                    'email' => ['The provided credentials are incorrect.'],
                ],
            ], 'Invalid credentials', 401);
        }

        return $this->apiResponse([
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
        ], 'Login successful');
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return $this->apiResponse(null, 'Logged out successfully');
    }

    public function user(Request $request): JsonResponse
    {
        return $this->apiResponse(new UserResource($request->user()));
    }
}
