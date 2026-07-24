<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('allows pending users to read their status but blocks learning routes', function (): void {
    $pendingUser = User::factory()->pending()->create([
        'telegram_username' => 'pending_student',
    ]);

    Sanctum::actingAs($pendingUser);

    $this->getJson('/api/v1/me/account-status')
        ->assertOk()
        ->assertJsonPath('data.accountStatus', 'pending')
        ->assertJsonPath('data.telegramUsername', 'pending_student');

    $this->getJson('/api/v1/me/learning-summary')
        ->assertForbidden()
        ->assertJsonPath('accountStatus', 'pending');
});

it('resets approval when a user changes Telegram username', function (): void {
    $approvedUser = User::factory()->create([
        'telegram_username' => 'approved_student',
    ]);

    Sanctum::actingAs($approvedUser);

    $this->patchJson('/api/v1/me', [
        'telegramUsername' => '@New_Student_Name',
    ])->assertOk()
        ->assertJsonPath('data.telegramUsername', 'new_student_name')
        ->assertJsonPath('data.accountStatus', 'pending');

    $this->getJson('/api/v1/me/learning-summary')
        ->assertForbidden()
        ->assertJsonPath('accountStatus', 'pending');
});

it('does not expose account approval as API endpoints', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->getJson('/api/v1/admin/users/pending')->assertNotFound();
    $this->patchJson('/api/v1/admin/users/1/approve')->assertNotFound();
    $this->patchJson('/api/v1/admin/users/1/reject', [
        'rejectionReason' => 'Invalid account.',
    ])->assertNotFound();
});
