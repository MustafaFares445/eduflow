<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Data\Auth\LoginData;
use App\Data\Auth\RegisterData;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final class AuthService
{
    public function register(RegisterData $data): array
    {
        $user = User::create([
            'name' => $data->name,
            'email' => $data->email,
            'password' => $data->password,
            'phone' => $data->phone,
            'locale' => $data->locale ?? 'en',
            'timezone' => $data->timezone,
            'is_active' => true,
            'last_login_at' => now(),
        ]);

        $user->notificationPreference()->create();

        return [
            'user' => $user->fresh(['notificationPreference', 'media']),
            'token' => $user->createToken('api')->plainTextToken,
        ];
    }

    public function login(LoginData $data): array
    {
        $user = User::query()->where('email', $data->email)->first();

        if (! $user || ! Hash::check($data->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => __('The provided credentials are incorrect.'),
            ]);
        }

        $user->forceFill(['last_login_at' => now()])->save();

        return [
            'user' => $user->fresh(['notificationPreference', 'media']),
            'token' => $user->createToken($data->deviceName ?? 'api')->plainTextToken,
        ];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }

    public function logoutAll(User $user): void
    {
        $user->tokens()->delete();
    }
}
