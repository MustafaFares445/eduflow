<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('registers, authenticates, and updates the current user profile', function (): void {
    $register = $this->postJson('/api/v1/auth/register', [
        'name' => 'EduFlow Student',
        'email' => 'student@example.com',
        'password' => 'password123',
        'phone' => '+963999999999',
        'locale' => 'en',
        'timezone' => 'Asia/Damascus',
    ]);

    $register->assertCreated()
        ->assertJsonPath('user.email', 'student@example.com')
        ->assertJsonStructure([
            'user' => ['id', 'name', 'email', 'createdAt', 'updatedAt'],
            'token',
        ]);

    $token = $register->json('token');

    $this->withToken($token)
        ->getJson('/api/v1/me')
        ->assertOk()
        ->assertJsonPath('data.email', 'student@example.com');

    $this->withToken($token)
        ->patchJson('/api/v1/me', [
            'name' => 'Updated Student',
            'bio' => 'Learning with EduFlow',
            'locale' => 'ar',
        ])
        ->assertOk()
        ->assertJsonPath('data.name', 'Updated Student')
        ->assertJsonPath('data.bio', 'Learning with EduFlow');
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
