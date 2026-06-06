<?php

declare(strict_types=1);

use App\Enums\Learning\CourseLevel;
use App\Enums\Learning\CourseStatus;
use App\Enums\Learning\CourseVisibility;
use App\Enums\Learning\LessonType;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('uploads course and lesson media and manages notifications', function (): void {
    Storage::fake('public');

    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $category = CourseCategory::create([
        'parent_id' => null,
        'name' => 'Languages',
        'slug' => 'languages',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $course = Course::create([
        'category_id' => $category->id,
        'instructor_id' => $user->id,
        'title' => 'Arabic 101',
        'slug' => 'arabic-101',
        'short_description' => 'Language course',
        'description' => 'Course description',
        'level' => CourseLevel::Beginner->value,
        'language' => 'ar',
        'status' => CourseStatus::Published->value,
        'visibility' => CourseVisibility::Public->value,
        'published_at' => now(),
    ]);

    $module = CourseModule::create([
        'course_id' => $course->id,
        'title' => 'Pronunciation',
        'description' => null,
        'position' => 1,
        'is_active' => true,
    ]);

    $lesson = Lesson::create([
        'course_id' => $course->id,
        'course_module_id' => $module->id,
        'title' => 'Alphabet video',
        'slug' => 'alphabet-video',
        'type' => LessonType::Video->value,
        'body' => 'Lesson body',
        'duration_seconds' => 120,
        'position' => 1,
        'is_preview' => true,
        'is_active' => true,
        'published_at' => now(),
    ]);

    $this->post('/api/v1/courses/'.$course->id.'/media', [
        'cover' => UploadedFile::fake()->image('cover.jpg'),
    ])->assertOk()
        ->assertJsonPath('data.cover.0.collectionName', 'cover');

    $this->post('/api/v1/lessons/'.$lesson->id.'/media', [
        'video' => UploadedFile::fake()->create('lesson.mp4', 1024, 'video/mp4'),
    ])->assertOk()
        ->assertJsonPath('data.video.0.collectionName', 'video');

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

    $readResponse = $this->patchJson('/api/v1/notifications/'.$firstNotificationId.'/read');
    $readResponse->assertOk()
        ->assertJsonPath('id', $firstNotificationId);

    expect($readResponse->json('readAt'))->not->toBeNull();

    $this->patchJson('/api/v1/notifications/read-all')
        ->assertOk();

    $notifications = $this->getJson('/api/v1/notifications')->assertOk()->json('data');

    expect($notifications[0]['readAt'])->not->toBeNull();
    expect($notifications[1]['readAt'])->not->toBeNull();
});
