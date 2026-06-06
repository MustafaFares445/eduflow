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
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('enrolls a user and syncs lesson progress', function (): void {
    $user = User::factory()->create();
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

    $this->postJson('/api/v1/courses/'.$course->id.'/enroll')
        ->assertOk()
        ->assertJsonPath('data.status', 'active')
        ->assertJsonPath('data.progressPercentage', '0.00');

    $this->patchJson('/api/v1/lessons/'.$lesson->id.'/progress', [
        'status' => LessonProgressStatus::Completed->value,
        'progressSeconds' => 600,
    ])
        ->assertOk()
        ->assertJsonPath('data.status', LessonProgressStatus::Completed->value);

    $this->getJson('/api/v1/me/enrollments')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.status', 'completed');

    $enrollment = Enrollment::query()->firstOrFail();

    $this->getJson('/api/v1/me/enrollments/'.$enrollment->id)
        ->assertOk()
        ->assertJsonPath('data.status', 'completed');
});
