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

uses(RefreshDatabase::class);

it('returns the catalog tree and only public published courses', function (): void {
    $root = CourseCategory::create([
        'parent_id' => null,
        'name' => 'Academic Year 1',
        'slug' => 'academic-year-1',
        'description' => 'Root academic year',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $child = CourseCategory::create([
        'parent_id' => $root->id,
        'name' => 'Mathematics',
        'slug' => 'mathematics',
        'description' => 'Subject category',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $instructor = User::factory()->create();

    $visibleCourse = Course::create([
        'category_id' => $child->id,
        'instructor_id' => $instructor->id,
        'title' => 'Visible Course',
        'slug' => 'visible-course',
        'short_description' => 'Shown in catalog',
        'description' => 'Public published course',
        'level' => CourseLevel::Beginner->value,
        'language' => 'en',
        'status' => CourseStatus::Published->value,
        'visibility' => CourseVisibility::Public->value,
        'published_at' => now(),
    ]);

    $hiddenCourse = Course::create([
        'category_id' => $child->id,
        'instructor_id' => $instructor->id,
        'title' => 'Hidden Course',
        'slug' => 'hidden-course',
        'short_description' => 'Hidden from catalog',
        'description' => 'Draft private course',
        'level' => CourseLevel::Beginner->value,
        'language' => 'en',
        'status' => CourseStatus::Draft->value,
        'visibility' => CourseVisibility::Private->value,
    ]);

    $module = CourseModule::create([
        'course_id' => $visibleCourse->id,
        'title' => 'Module 1',
        'description' => 'Module description',
        'position' => 1,
        'is_active' => true,
    ]);

    Lesson::create([
        'course_id' => $visibleCourse->id,
        'course_module_id' => $module->id,
        'title' => 'Lesson 1',
        'slug' => 'lesson-1',
        'type' => LessonType::Video->value,
        'body' => 'Lesson body',
        'duration_seconds' => 300,
        'position' => 1,
        'is_preview' => true,
        'is_active' => true,
        'published_at' => now(),
    ]);

    $this->getJson('/api/v1/catalog/categories')
        ->assertOk()
        ->assertJsonPath('data.0.name', 'Academic Year 1')
        ->assertJsonPath('data.0.children.0.name', 'Mathematics');

    $this->getJson('/api/v1/catalog/courses')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'Visible Course');

    $this->getJson('/api/v1/catalog/courses/'.$visibleCourse->id)
        ->assertOk()
        ->assertJsonPath('data.title', 'Visible Course')
        ->assertJsonPath('data.modules.0.title', 'Module 1');
});
