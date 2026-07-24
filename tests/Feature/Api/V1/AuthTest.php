<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('registers by phone, verifies otp, logs in, and updates the profile', function (): void {
    $register = $this->postJson('/api/v1/auth/register', [
        'full_name' => 'EduFlow Student',
        'phone' => '+963999999999',
        'password' => 'password123',
        'locale' => 'ar',
        'timezone' => 'Asia/Damascus',
    ]);

    $register->assertCreated()
        ->assertJsonPath('user.name', 'EduFlow Student')
        ->assertJsonPath('user.phone', '+963999999999')
        ->assertJsonStructure(['user', 'message', 'verificationExpiresAt', 'debugOtp']);

    $otp = $register->json('debugOtp');

    $verify = $this->postJson('/api/v1/auth/verify-otp', [
        'phone' => '+963999999999',
        'otp' => $otp,
        'deviceName' => 'Flutter test',
    ]);

    $verify->assertOk()
        ->assertJsonPath('user.phone', '+963999999999')
        ->assertJsonStructure(['user', 'token', 'message']);

    $token = $verify->json('token');

    $this->withToken($token)
        ->getJson('/api/v1/me')
        ->assertOk()
        ->assertJsonPath('data.phone', '+963999999999');

    $this->withToken($token)
        ->patchJson('/api/v1/me', [
            'fullName' => 'Updated Student',
            'bio' => 'Learning with EduFlow',
            'locale' => 'ar',
        ])
        ->assertOk()
        ->assertJsonPath('data.name', 'Updated Student')
        ->assertJsonPath('data.bio', 'Learning with EduFlow');

    $this->postJson('/api/v1/auth/login', [
        'phone' => '+963999999999',
        'password' => 'password123',
    ])->assertOk()->assertJsonStructure(['user', 'token', 'message']);
});

it('does not allow login before phone verification', function (): void {
    $user = User::factory()->create([
        'phone' => '+963911111111',
        'phone_verified_at' => null,
        'password' => 'password123',
    ]);

    $this->postJson('/api/v1/auth/login', [
        'phone' => $user->phone,
        'password' => 'password123',
    ])->assertUnprocessable()->assertJsonValidationErrors('phone');
});

it('uploads an avatar for the authenticated user', function (): void {
    Storage::fake('public');

    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->post('/api/v1/me/avatar', [
        'avatar' => UploadedFile::fake()->image('avatar.jpg'),
    ]);

    $response->assertOk();

    expect($response->json('data.avatarUrl'))->toBeString()->not->toBe('');
});
