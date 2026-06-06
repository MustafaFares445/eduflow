<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Data\Assessment\AttemptAnswerData;
use App\Enums\Assessment\AssessmentType;
use App\Enums\Assessment\QuestionType;
use App\Enums\Learning\CourseLevel;
use App\Enums\Learning\CourseStatus;
use App\Enums\Learning\CourseVisibility;
use App\Enums\Learning\LessonProgressStatus;
use App\Enums\Learning\LessonType;
use App\Enums\Notification\NotificationChannel;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\NotificationTemplate;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\User;
use App\Services\Assessment\AssessmentAttemptService;
use App\Services\Learning\CourseService;
use App\Services\Learning\LessonProgressService;
use Illuminate\Database\Seeder;

final class EduFlowDemoSeeder extends Seeder
{
    public function run(): void
    {
        $instructor = User::query()->firstOrCreate(
            ['email' => 'instructor@eduflow.local'],
            [
                'name' => 'EduFlow Instructor',
                'password' => 'password123',
                'locale' => 'en',
                'timezone' => 'Asia/Damascus',
                'is_active' => true,
            ]
        );

        $student = User::query()->firstOrCreate(
            ['email' => 'student@eduflow.local'],
            [
                'name' => 'EduFlow Student',
                'password' => 'password123',
                'locale' => 'en',
                'timezone' => 'Asia/Damascus',
                'is_active' => true,
            ]
        );

        $student->notificationPreference()->firstOrCreate([], [
            'email_enabled' => true,
            'push_enabled' => true,
            'in_app_enabled' => true,
            'course_updates_enabled' => true,
            'assessment_updates_enabled' => true,
        ]);

        $academicYear = CourseCategory::query()->firstOrCreate(
            ['slug' => 'academic-year-1'],
            [
                'parent_id' => null,
                'name' => 'Academic Year 1',
                'description' => 'Primary academic year',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        $subject = CourseCategory::query()->firstOrCreate(
            ['slug' => 'mathematics'],
            [
                'parent_id' => $academicYear->id,
                'name' => 'Mathematics',
                'description' => 'Core math subject',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        $course = Course::query()->firstOrCreate(
            ['slug' => 'foundations-of-maths'],
            [
                'category_id' => $subject->id,
                'instructor_id' => $instructor->id,
                'title' => 'Foundations of Maths',
                'short_description' => 'A beginner course for core math skills.',
                'description' => 'This demo course powers the EduFlow sample content.',
                'level' => CourseLevel::Beginner->value,
                'language' => 'en',
                'status' => CourseStatus::Published->value,
                'visibility' => CourseVisibility::Public->value,
                'published_at' => now(),
            ]
        );

        $module = CourseModule::query()->firstOrCreate(
            ['course_id' => $course->id, 'position' => 1],
            [
                'title' => 'Numbers and Sets',
                'description' => 'Module one overview',
                'is_active' => true,
            ]
        );

        $lesson = Lesson::query()->firstOrCreate(
            ['course_module_id' => $module->id, 'slug' => 'introduction-to-numbers'],
            [
                'course_id' => $course->id,
                'title' => 'Introduction to Numbers',
                'type' => LessonType::Video->value,
                'body' => 'Demo lesson body',
                'duration_seconds' => 420,
                'position' => 1,
                'is_preview' => true,
                'is_active' => true,
                'published_at' => now(),
            ]
        );

        $assessment = Assessment::query()->firstOrCreate(
            ['course_id' => $course->id, 'position' => 1],
            [
                'lesson_id' => $lesson->id,
                'title' => 'Demo Quiz',
                'description' => 'Sample assessment for the demo course',
                'type' => AssessmentType::Quiz->value,
                'status' => 'published',
                'passing_score' => 60,
                'max_attempts' => 3,
                'time_limit_minutes' => 15,
                'shuffle_questions' => false,
                'show_result_immediately' => true,
            ]
        );

        $question = Question::query()->firstOrCreate(
            ['assessment_id' => $assessment->id, 'position' => 1],
            [
                'type' => QuestionType::MultipleChoice->value,
                'question_text' => 'Which command installs the API scaffolding?',
                'explanation' => 'Laravel 13 uses install:api.',
                'points' => 5,
                'is_active' => true,
            ]
        );

        $correctOption = QuestionOption::query()->firstOrCreate(
            ['question_id' => $question->id, 'position' => 1],
            [
                'option_text' => 'php artisan install:api',
                'is_correct' => true,
            ]
        );

        QuestionOption::query()->firstOrCreate(
            ['question_id' => $question->id, 'position' => 2],
            [
                'option_text' => 'php artisan make:api',
                'is_correct' => false,
            ]
        );

        $courseService = app(CourseService::class);
        $courseService->syncStatistics($course->fresh());

        $student->enrollments()->firstOrCreate(
            ['course_id' => $course->id],
            [
                'status' => 'active',
                'progress_percentage' => 0,
                'enrolled_at' => now(),
                'last_accessed_at' => now(),
            ]
        );

        app(LessonProgressService::class)->complete($student, $lesson);

        $attemptService = app(AssessmentAttemptService::class);
        if (! AssessmentAttempt::query()->where('assessment_id', $assessment->id)->where('user_id', $student->id)->exists()) {
            $attempt = $attemptService->start($assessment, $student);
            $attemptService->saveAnswer($attempt, new AttemptAnswerData(
                questionId: $question->id,
                selectedOptionId: $correctOption->id,
            ));
            $attemptService->submit($attempt->fresh());
        }

        NotificationTemplate::query()->firstOrCreate(
            ['key' => 'course.published'],
            [
                'title' => 'Course published',
                'body' => 'A new course is available in EduFlow.',
                'channels' => [NotificationChannel::Database->value],
                'variables' => ['courseTitle'],
                'is_active' => true,
            ]
        );
    }
}
