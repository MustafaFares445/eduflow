<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Data\Auth\LoginData;
use App\Data\Auth\RegisterData;
use App\Data\Auth\VerifyOtpData;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final class AuthService
{
    public function register(RegisterData $data): array
    {
        $otp = $this->generateOtp();
        $phone = $this->normalizePhone($data->phone);
        $emailKey = preg_replace('/\D+/', '', $phone) ?: sha1($phone);

        $user = User::create([
            'name' => $data->fullName,
            'email' => $emailKey.'@phone.eduflow.local',
            'password' => $data->password,
            'phone' => $phone,
            'phone_verified_at' => null,
            'verification_code' => Hash::make($otp),
            'verification_code_expires_at' => now()->addMinutes(10),
            'locale' => $data->locale ?? 'ar',
            'timezone' => $data->timezone,
            'is_active' => true,
        ]);

        $user->notificationPreference()->create();

        // Replace this with the project's SMS gateway before production launch.
        return [
            'user' => $user->fresh(['notificationPreference', 'media']),
            'verificationExpiresAt' => $user->verification_code_expires_at?->toISOString(),
            'debugOtp' => app()->environment(['local', 'testing']) ? $otp : null,
        ];
    }

    public function resendOtp(string $phone): array
    {
        $user = User::query()->where('phone', $this->normalizePhone($phone))->firstOrFail();
        $otp = $this->generateOtp();

        $user->forceFill([
            'verification_code' => Hash::make($otp),
            'verification_code_expires_at' => now()->addMinutes(10),
        ])->save();

        return [
            'verificationExpiresAt' => $user->verification_code_expires_at?->toISOString(),
            'debugOtp' => app()->environment(['local', 'testing']) ? $otp : null,
        ];
    }

    public function verifyOtp(VerifyOtpData $data): array
    {
        $user = User::query()->where('phone', $this->normalizePhone($data->phone))->first();

        if (! $user
            || $user->verification_code === null
            || $user->verification_code_expires_at === null
            || $user->verification_code_expires_at->isPast()
            || ! Hash::check($data->otp, $user->verification_code)) {
            throw ValidationException::withMessages([
                'otp' => __('The verification code is invalid or expired.'),
            ]);
        }

        $user->forceFill([
            'phone_verified_at' => now(),
            'verification_code' => null,
            'verification_code_expires_at' => null,
            'last_login_at' => now(),
        ])->save();

        return $this->authenticatedResponse($user, $data->deviceName);
    }

    public function login(LoginData $data): array
    {
        $user = User::query()->where('phone', $this->normalizePhone($data->phone))->first();

        if (! $user || ! Hash::check($data->password, $user->password)) {
            throw ValidationException::withMessages([
                'phone' => __('The provided credentials are incorrect.'),
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'phone' => __('This account has been deactivated.'),
            ]);
        }

        if ($user->phone_verified_at === null) {
            throw ValidationException::withMessages([
                'phone' => __('Phone verification is required.'),
            ]);
        }

        $user->forceFill(['last_login_at' => now()])->save();

        return $this->authenticatedResponse($user, $data->deviceName);
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }

    public function logoutAll(User $user): void
    {
        $user->tokens()->delete();
    }

    private function authenticatedResponse(User $user, ?string $deviceName): array
    {
        return [
            'user' => $user->fresh(['notificationPreference', 'media']),
            'token' => $user->createToken($deviceName ?: 'flutter-app')->plainTextToken,
        ];
    }

    private function normalizePhone(string $phone): string
    {
        return preg_replace('/\s+/', '', trim($phone)) ?: trim($phone);
    }

    private function generateOtp(): string
    {
        return (string) random_int(100000, 999999);
    }
}
