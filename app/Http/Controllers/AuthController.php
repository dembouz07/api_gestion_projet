<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\AuthService;
use App\Services\ElasticsearchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    protected $authService;
    protected $elasticsearchService;

    public function __construct(ElasticsearchService $elasticsearchService)
    {
        $this->authService = new AuthService();
        $this->elasticsearchService = $elasticsearchService;
    }

    public function register(RegisterRequest $request)
    {
        try {
            $result = $this->authService->register($request->validated());

            $this->elasticsearchService->logUserActivity('user_registered', [
                'email' => $request->validated()['email'],
            ]);

            $this->elasticsearchService->logMetric('user_registration', [
                'success' => true,
            ]);

            Log::info('User registered successfully', [
                'email' => $request->validated()['email'],
            ]);

            return response()->json($result, 201);
        } catch (\Exception $e) {
            Log::error('Registration failed', [
                'email' => $request->validated()['email'] ?? 'unknown',
                'error' => $e->getMessage()
            ]);

            $this->elasticsearchService->logMetric('user_registration', [
                'success' => false,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function login(LoginRequest $request)
    {
        try {
            $result = $this->authService->login($request->validated());

            $this->elasticsearchService->logUserActivity('user_logged_in', [
                'email' => $request->validated()['email'],
            ]);

            $this->elasticsearchService->logMetric('user_login', [
                'success' => true,
                'user_id' => auth()->id(),
            ]);

            Log::info('User logged in successfully', [
                'email' => $request->validated()['email'],
                'user_id' => auth()->id(),
            ]);

            return response()->json($result, 200);
        } catch (\Exception $e) {
            Log::warning('Login attempt failed', [
                'email' => $request->validated()['email'] ?? 'unknown',
                'error' => $e->getMessage()
            ]);

            $this->elasticsearchService->logMetric('user_login', [
                'success' => false,
                'email' => $request->validated()['email'] ?? 'unknown',
            ]);

            return response()->json([
                'message' => 'Login failed',
                'error' => $e->getMessage()
            ], 401);
        }
    }

    public function logout()
    {
        try {
            $userId = auth()->id();
            $userEmail = auth()->user()->email ?? 'unknown';

            $this->elasticsearchService->logUserActivity('user_logged_out', [
                'user_id' => $userId,
            ]);

            $this->elasticsearchService->logMetric('user_logout', [
                'user_id' => $userId,
            ]);

            $result = $this->authService->logout();

            Log::info('User logged out', [
                'user_id' => $userId,
                'email' => $userEmail,
            ]);

            return response()->json($result, 200);
        } catch (\Exception $e) {
            Log::error('Logout failed', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Logout failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function me()
    {
        try {
            $this->elasticsearchService->logUserActivity('viewed_profile');
            return response()->json($this->authService->getAuthenticatedUser(), 200);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve authenticated user', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Failed to retrieve user',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
