<?php

declare(strict_types=1);

use App\Enums\Auth\AccountStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('allows an admin to list and approve pending users', function (): void {
    $admin = User::factory()->admin()->create();
    $pendingUser = User::factory()->pending()->create([
        'telegram_username' => 'pending_student',
    ]);

    Sanctum::actingAs($admin);

    $this->getJson('/api/v1/admin/users/pending')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $pendingUser->id)
        ->assertJsonPath('data.0.telegramUsername', 'pending_student');

    $this->patchJson('/api/v1/admin/users/'.$pendingUser->id.'/approve')
        ->assertOk()
        ->assertJsonPath('data.accountStatus', 'approved')
        ->assertJsonPath('data.isApproved', true);

    expect($pendingUser->fresh()->account_status)->toBe(AccountStatus::Approved);

    Sanctum::actingAs($pendingUser->fresh());

    $this->getJson('/api/v1/me/learning-summary')->assertOk();
});

it('allows an admin to reject a user and returns the reason to Flutter', function (): void {
    $admin = User::factory()->admin()->create();
    $pendingUser = User::factory()->pending()->create();

    Sanctum::actingAs($admin);

    $this->patchJson('/api/v1/admin/users/'.$pendingUser->id.'/reject', [
        'rejectionReason' => 'Telegram account could not be verified.',
    ])->assertOk()
        ->assertJsonPath('data.accountStatus', 'rejected')
        ->assertJsonPath('data.rejectionReason', 'Telegram account could not be verified.');

    Sanctum::actingAs($pendingUser->fresh());

    $this->getJson('/api/v1/me/learning-summary')
        ->assertForbidden()
        ->assertJsonPath('accountStatus', 'rejected')
        ->assertJsonPath('rejectionReason', 'Telegram account could not be verified.');
});

it('prevents non-admin users from reviewing accounts', function (): void {
    $user = User::factory()->create();
    $pendingUser = User::factory()->pending()->create();

    Sanctum::actingAs($user);

    $this->patchJson('/api/v1/admin/users/'.$pendingUser->id.'/approve')->assertForbidden();
});
