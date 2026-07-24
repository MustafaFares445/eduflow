<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('lists and marks the authenticated users notifications as read', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $firstNotificationId = (string) Str::uuid();
    $secondNotificationId = (string) Str::uuid();

    $user->notifications()->create([
        'id' => $firstNotificationId,
        'type' => 'course-updated',
        'data' => ['title' => 'Course updated'],
    ]);

    $user->notifications()->create([
        'id' => $secondNotificationId,
        'type' => 'lesson-updated',
        'data' => ['title' => 'Lesson updated'],
    ]);

    $this->getJson('/api/v1/notifications')
        ->assertOk()
        ->assertJsonCount(2, 'data');

    $this->patchJson('/api/v1/notifications/'.$firstNotificationId.'/read')
        ->assertOk()
        ->assertJsonPath('data.id', $firstNotificationId);

    $this->patchJson('/api/v1/notifications/read-all')->assertOk();

    $notifications = $this->getJson('/api/v1/notifications')->assertOk()->json('data');

    expect($notifications[0]['readAt'])->not->toBeNull();
    expect($notifications[1]['readAt'])->not->toBeNull();
});

it('cannot read another users notification', function (): void {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $notificationId = (string) Str::uuid();

    $owner->notifications()->create([
        'id' => $notificationId,
        'type' => 'course-updated',
        'data' => ['title' => 'Course updated'],
    ]);

    Sanctum::actingAs($otherUser);

    $this->patchJson('/api/v1/notifications/'.$notificationId.'/read')->assertNotFound();
});
