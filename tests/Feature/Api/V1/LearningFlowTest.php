<?php

declare(strict_types=1);

use App\Enums\Learning\CourseLevel;
use App\Enums\Learning\CourseStatus;
use App\Enums\Learning\CourseVisibility;
use App\Enums\Learning\LessonProgressStatus;
use App\Enums\Learning\LessonType;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\CourseModule;
use App\Models\Enrollment;
use App\Models\EnrollmentCode;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('redeems a QR code and returns personalized learning progress', function (): void {
    $user = User::factory()->create(['phone_verified_at' => now()]);
    Sanctum::actingAs($user);

    $category = CourseCategory::create([
        'parent_id' => null,
        'name' => 'Science',
        'slug' => 'science',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $course = Course::create([
        'category_id' => $category->id,
        'instructor_id' => $user->id,
        'title' => 'Physics 101',
        'slug' => 'physics-101',
        'short_description' => 'Introductory physics',
        'description' => 'Course description',
        'level' => CourseLevel::Beginner->value,
        'language' => 'en',
        'status' => CourseStatus::Published->value,
        'visibility' => CourseVisibility::Public->value,
        'published_at' => now(),
    ]);

    $module = CourseModule::create([
        'course_id' => $course->id,
        'title' => 'Fundamentals',
        'description' => null,
        'position' => 1,
        'is_active' => true,
    ]);

    $lesson = Lesson::create([
        'course_id' => $course->id,
        'course_module_id' => $module->id,
        'title' => 'Introduction',
        'slug' => 'introduction',
        'type' => LessonType::Video->value,
        'body' => 'Lesson body',
        'duration_seconds' => 600,
        'position' => 1,
        'is_preview' => false,
        'is_active' => true,
        'published_at' => now(),
    ]);

    EnrollmentCode::create([
        'course_id' => $course->id,
        'code_hash' => EnrollmentCode::hash('PHYSICS-2026'),
        'access_days' => 30,
        'max_redemptions' => 1,
        'is_active' => true,
    ]);

    $this->postJson('/api/v1/enrollment-codes/redeem', ['code' => 'PHYSICS-2026'])
        ->assertOk()
        ->assertJsonPath('data.status', 'active')
        ->assertJsonPath('data.courseId', $course->id)
        ->assertJsonPath('data.isExpired', false);

    // Redemption is idempotent for the same user.
    $this->postJson('/api/v1/enrollment-codes/redeem', ['code' => 'PHYSICS-2026'])
        ->assertOk();

    $this->patchJson('/api/v1/lessons/'.$lesson->id.'/progress', [
        'status' => LessonProgressStatus::Completed->value,
        'progressSeconds' => 600,
    ])->assertOk()->assertJsonPath('data.status', LessonProgressStatus::Completed->value);

    $this->getJson('/api/v1/me/courses/'.$course->id)
        ->assertOk()
        ->assertJsonPath('data.course.title', 'Physics 101')
        ->assertJsonPath('data.modules.0.lessons.0.progress.status', 'completed')
        ->assertJsonPath('data.summary.completedLessonsCount', 1);

    $this->getJson('/api/v1/me/learning-summary')
        ->assertOk()
        ->assertJsonPath('data.coursesCount', 1)
        ->assertJsonPath('data.completedLessonsCount', 1)
        ->assertJsonPath('data.learningMinutes', 10);

    $enrollment = Enrollment::query()->firstOrFail();
    expect($enrollment->expires_at)->not->toBeNull();
});

it('rejects progress updates without active course access', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $category = CourseCategory::create([
        'name' => 'Math',
        'slug' => 'math',
        'is_active' => true,
        'sort_order' => 1,
    ]);
    $course = Course::create([
        'category_id' => $category->id,
        'title' => 'Math course',
        'slug' => 'math-course',
        'level' => CourseLevel::Beginner->value,
        'status' => CourseStatus::Published->value,
        'visibility' => CourseVisibility::Public->value,
    ]);
    $module = CourseModule::create([
        'course_id' => $course->id,
        'title' => 'Module',
        'position' => 1,
        'is_active' => true,
    ]);
    $lesson = Lesson::create([
        'course_id' => $course->id,
        'course_module_id' => $module->id,
        'title' => 'Lesson',
        'slug' => 'lesson',
        'type' => LessonType::Video->value,
        'duration_seconds' => 60,
        'position' => 1,
        'is_active' => true,
    ]);

    $this->patchJson('/api/v1/lessons/'.$lesson->id.'/progress', [
        'status' => 'in_progress',
        'progressSeconds' => 20,
    ])->assertNotFound();
});
