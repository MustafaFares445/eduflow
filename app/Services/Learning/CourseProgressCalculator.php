<?php

declare(strict_types=1);

namespace App\Services\Learning;

use App\Enums\Learning\EnrollmentStatus;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;

final class CourseProgressCalculator
{
    public function syncEnrollment(Enrollment $enrollment): Enrollment
    {
        $course = $enrollment->course()->firstOrFail();

        $totalLessons = Lesson::query()
            ->where('course_id', $course->id)
            ->where('is_active', true)
            ->count();

        $completedLessons = LessonProgress::query()
            ->where('user_id', $enrollment->user_id)
            ->where('course_id', $course->id)
            ->where('status', 'completed')
            ->count();

        $progress = $totalLessons > 0
            ? round(($completedLessons / $totalLessons) * 100, 2)
            : 0.0;

        $enrollment->forceFill([
            'progress_percentage' => $progress,
            'last_accessed_at' => now(),
            'status' => $progress >= 100 ? EnrollmentStatus::Completed : EnrollmentStatus::Active,
            'completed_at' => $progress >= 100 ? ($enrollment->completed_at ?? now()) : $enrollment->completed_at,
        ])->save();

        return $enrollment->fresh(['user', 'course']);
    }
}
