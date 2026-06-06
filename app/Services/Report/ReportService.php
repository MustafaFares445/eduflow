<?php

declare(strict_types=1);

namespace App\Services\Report;

use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Enrollment;
use App\Models\LessonProgress;
use App\Models\User;

final class ReportService
{
    public function learningOverview(): array
    {
        return [
            'coursesTotal' => Course::query()->count(),
            'publishedCourses' => Course::query()->where('status', 'published')->count(),
            'categoriesTotal' => CourseCategory::query()->count(),
            'enrollmentsTotal' => Enrollment::query()->count(),
            'activeEnrollments' => Enrollment::query()->where('status', 'active')->count(),
            'completedLessons' => LessonProgress::query()->where('status', 'completed')->count(),
            'assessmentsTotal' => Assessment::query()->count(),
            'attemptsTotal' => AssessmentAttempt::query()->count(),
            'averageAssessmentScore' => round((float) (AssessmentAttempt::query()->avg('percentage') ?? 0), 2),
        ];
    }

    public function course(Course $course): array
    {
        $course->loadCount(['modules', 'lessons', 'assessments', 'enrollments']);

        return [
            'course' => [
                'id' => $course->id,
                'title' => $course->title,
                'status' => $course->status?->value ?? $course->status,
                'visibility' => $course->visibility?->value ?? $course->visibility,
                'lessonsCount' => (int) $course->lessons_count,
                'studentsCount' => (int) $course->students_count,
                'averageRating' => $course->average_rating,
                'ratingsCount' => (int) $course->ratings_count,
            ],
            'enrollments' => [
                'total' => $course->enrollments()->count(),
                'active' => $course->enrollments()->where('status', 'active')->count(),
                'completed' => $course->enrollments()->where('status', 'completed')->count(),
            ],
            'lessons' => [
                'total' => $course->lessons()->count(),
                'completed' => LessonProgress::query()->where('course_id', $course->id)->where('status', 'completed')->count(),
            ],
            'assessments' => [
                'total' => $course->assessments()->count(),
                'attempts' => AssessmentAttempt::query()
                    ->whereIn('assessment_id', $course->assessments()->select('id'))
                    ->count(),
            ],
        ];
    }

    public function userProgress(User $user): array
    {
        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'enrollmentsTotal' => Enrollment::query()->where('user_id', $user->id)->count(),
            'activeEnrollments' => Enrollment::query()->where('user_id', $user->id)->where('status', 'active')->count(),
            'completedEnrollments' => Enrollment::query()->where('user_id', $user->id)->where('status', 'completed')->count(),
            'completedLessons' => LessonProgress::query()->where('user_id', $user->id)->where('status', 'completed')->count(),
            'attemptsTotal' => AssessmentAttempt::query()->where('user_id', $user->id)->count(),
            'averageAssessmentScore' => round((float) (AssessmentAttempt::query()->where('user_id', $user->id)->avg('percentage') ?? 0), 2),
        ];
    }

    public function assessment(Assessment $assessment): array
    {
        $attempts = AssessmentAttempt::query()->where('assessment_id', $assessment->id);
        $attemptCount = $attempts->count();
        $submittedCount = (clone $attempts)->whereNotNull('submitted_at')->count();
        $averageScore = (float) ((clone $attempts)->avg('percentage') ?? 0);
        $passedCount = (clone $attempts)->where('is_passed', true)->count();

        return [
            'assessment' => [
                'id' => $assessment->id,
                'title' => $assessment->title,
                'status' => $assessment->status?->value ?? $assessment->status,
                'passingScore' => (int) $assessment->passing_score,
            ],
            'attemptsTotal' => $attemptCount,
            'submittedAttempts' => $submittedCount,
            'passRate' => $attemptCount > 0 ? round(($passedCount / $attemptCount) * 100, 2) : 0.0,
            'averageScore' => round($averageScore, 2),
            'maxAttempts' => $assessment->max_attempts,
        ];
    }

    public function exportLearning(): array
    {
        return [
            'generatedAt' => now()->toISOString(),
            'overview' => $this->learningOverview(),
        ];
    }
}
