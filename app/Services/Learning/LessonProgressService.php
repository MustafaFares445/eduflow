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
        $enrollment = Enrollment::query()
            ->where('user_id', $user->id)
            ->where('course_id', $lesson->course_id)
            ->firstOrFail();

        abort_if($enrollment->isExpired(), 403, __('Course access has expired.'));

        $progressSeconds = min(max(0, $data->progressSeconds), (int) $lesson->duration_seconds);
        $isCompleted = $data->status === LessonProgressStatus::Completed->value
            || ($lesson->duration_seconds > 0 && $progressSeconds >= $lesson->duration_seconds);
        $status = $isCompleted
            ? LessonProgressStatus::Completed->value
            : ($progressSeconds > 0 ? LessonProgressStatus::InProgress->value : $data->status);

        $progress = LessonProgress::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'course_id' => $lesson->course_id,
                'lesson_id' => $lesson->id,
            ],
            [
                'status' => $status,
                'progress_seconds' => $progressSeconds,
                'completed_at' => $isCompleted ? now() : null,
                'last_accessed_at' => now(),
            ]
        );

        $enrollment->forceFill(['last_accessed_at' => now()])->save();
        $this->courseProgressCalculator->syncEnrollment($enrollment);

        return $progress->fresh(['user', 'course.category', 'lesson.media']);
    }

    public function complete(User $user, Lesson $lesson): LessonProgress
    {
        return $this->update($user, $lesson, new LessonProgressData(
            status: LessonProgressStatus::Completed->value,
            progressSeconds: $lesson->duration_seconds,
        ));
    }
}
