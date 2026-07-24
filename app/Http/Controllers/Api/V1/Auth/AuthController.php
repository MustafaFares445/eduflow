<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Data\Auth\LoginData;
use App\Data\Auth\RegisterData;
use App\Data\Auth\VerifyOtpData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Requests\Api\V1\Auth\RegisterRequest;
use App\Http\Requests\Api\V1\Auth\ResendOtpRequest;
use App\Http\Requests\Api\V1\Auth\VerifyOtpRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AuthController extends Controller
{
    public function register(RegisterRequest $request, AuthService $authService): JsonResponse
    {
        $result = $authService->register(RegisterData::from($request->validated()));

        return response()->json([
            'user' => UserResource::make($result['user']),
            'message' => __('Verification code sent successfully.'),
            'verificationExpiresAt' => $result['verificationExpiresAt'],
            'debugOtp' => $result['debugOtp'],
        ], 201);
    }

    public function resendOtp(ResendOtpRequest $request, AuthService $authService): JsonResponse
    {
        $result = $authService->resendOtp($request->validated('phone'));

        return response()->json([
            'message' => __('Verification code resent successfully.'),
            'verificationExpiresAt' => $result['verificationExpiresAt'],
            'debugOtp' => $result['debugOtp'],
        ]);
    }

    public function verifyOtp(VerifyOtpRequest $request, AuthService $authService): JsonResponse
    {
        $result = $authService->verifyOtp(VerifyOtpData::from($request->validated()));

        return response()->json([
            'user' => UserResource::make($result['user']),
            'token' => $result['token'],
            'message' => __('Phone verified successfully.'),
        ]);
    }

    public function login(LoginRequest $request, AuthService $authService): JsonResponse
    {
        $result = $authService->login(LoginData::from($request->validated()));

        return response()->json([
            'user' => UserResource::make($result['user']),
            'token' => $result['token'],
            'message' => __('Logged in successfully.'),
        ]);
    }

    public function logout(Request $request, AuthService $authService): JsonResponse
    {
        $authService->logout($request->user());

        return response()->json([
            'message' => __('Logged out successfully.'),
        ]);
    }

    public function logoutAll(Request $request, AuthService $authService): JsonResponse
    {
        $authService->logoutAll($request->user());

        return response()->json([
            'message' => __('Logged out from all devices.'),
        ]);
    }
}
