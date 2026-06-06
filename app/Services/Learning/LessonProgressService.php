<?php

declare(strict_types=1);

namespace App\Services\Learning;

use App\Data\Learning\LessonProgressData;
use App\Enums\Learning\LessonProgressStatus;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\User;

final class LessonProgressService
{
    public function __construct(
        private readonly CourseProgressCalculator $courseProgressCalculator,
    ) {}

    public function update(User $user, Lesson $lesson, LessonProgressData $data): LessonProgress
    {
        $enrollment = Enrollment::firstOrCreate(
            ['user_id' => $user->id, 'course_id' => $lesson->course_id],
            ['status' => 'active', 'progress_percentage' => 0, 'enrolled_at' => now()],
        );

        $progress = LessonProgress::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'course_id' => $lesson->course_id,
                'lesson_id' => $lesson->id,
            ],
            [
                'status' => $data->status,
                'progress_seconds' => $data->progressSeconds,
                'completed_at' => $data->status === LessonProgressStatus::Completed->value ? now() : null,
                'last_accessed_at' => now(),
            ]
        );

        $this->courseProgressCalculator->syncEnrollment($enrollment);

        return $progress->fresh(['user', 'course.category', 'lesson']);
    }

    public function complete(User $user, Lesson $lesson): LessonProgress
    {
        return $this->update($user, $lesson, new LessonProgressData(
            status: LessonProgressStatus::Completed->value,
            progressSeconds: $lesson->duration_seconds,
        ));
    }
}
