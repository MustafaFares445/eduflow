<?php

declare(strict_types=1);

use App\Enums\Assessment\AssessmentType;
use App\Enums\Assessment\QuestionType;
use App\Enums\Learning\CourseLevel;
use App\Enums\Learning\CourseStatus;
use App\Enums\Learning\CourseVisibility;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('starts, answers, and submits an assessment attempt', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $category = CourseCategory::create([
        'parent_id' => null,
        'name' => 'Computer Science',
        'slug' => 'computer-science',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $course = Course::create([
        'category_id' => $category->id,
        'instructor_id' => $user->id,
        'title' => 'Laravel Basics',
        'slug' => 'laravel-basics',
        'short_description' => 'Intro course',
        'description' => 'Course description',
        'level' => CourseLevel::Beginner->value,
        'language' => 'en',
        'status' => CourseStatus::Published->value,
        'visibility' => CourseVisibility::Public->value,
        'published_at' => now(),
    ]);

    $module = CourseModule::create([
        'course_id' => $course->id,
        'title' => 'Getting Started',
        'description' => null,
        'position' => 1,
        'is_active' => true,
    ]);

    Lesson::create([
        'course_id' => $course->id,
        'course_module_id' => $module->id,
        'title' => 'Welcome',
        'slug' => 'welcome',
        'type' => 'quiz',
        'body' => 'Lesson body',
        'duration_seconds' => 300,
        'position' => 1,
        'is_preview' => false,
        'is_active' => true,
        'published_at' => now(),
    ]);

    $assessment = Assessment::create([
        'course_id' => $course->id,
        'lesson_id' => null,
        'title' => 'Laravel Quiz',
        'description' => 'Check knowledge',
        'type' => AssessmentType::Quiz->value,
        'status' => 'published',
        'passing_score' => 60,
        'max_attempts' => 3,
        'time_limit_minutes' => 30,
        'shuffle_questions' => false,
        'show_result_immediately' => true,
        'position' => 1,
    ]);

    $question = Question::create([
        'assessment_id' => $assessment->id,
        'type' => QuestionType::MultipleChoice->value,
        'question_text' => 'Which command installs the API scaffolding?',
        'explanation' => 'Laravel 13 uses install:api.',
        'points' => 5,
        'position' => 1,
        'is_active' => true,
    ]);

    $correctOption = QuestionOption::create([
        'question_id' => $question->id,
        'option_text' => 'php artisan install:api',
        'is_correct' => true,
        'position' => 1,
    ]);

    QuestionOption::create([
        'question_id' => $question->id,
        'option_text' => 'php artisan make:api',
        'is_correct' => false,
        'position' => 2,
    ]);

    $attempt = $this->postJson('/api/v1/assessments/'.$assessment->id.'/attempts')
        ->assertOk()
        ->assertJsonPath('data.assessmentId', $assessment->id)
        ->json('data.id');

    $this->postJson('/api/v1/attempts/'.$attempt.'/answers', [
        'questionId' => $question->id,
        'selectedOptionId' => $correctOption->id,
    ])->assertOk()
        ->assertJsonPath('data.isCorrect', true);

    $this->postJson('/api/v1/attempts/'.$attempt.'/submit')
        ->assertOk()
        ->assertJsonPath('data.percentage', '100.00')
        ->assertJsonPath('data.isPassed', true);

    $this->getJson('/api/v1/attempts/'.$attempt.'/result')
        ->assertOk()
        ->assertJsonPath('data.score', '5.00')
        ->assertJsonPath('data.answers.0.isCorrect', true);
});
