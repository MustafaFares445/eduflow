<?php

declare(strict_types=1);

namespace App\Services\Learning;

use App\Enums\Learning\EnrollmentStatus;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class EnrollmentService
{
    public function __construct(
        private readonly CourseProgressCalculator $courseProgressCalculator,
        private readonly CourseService $courseService,
    ) {}

    public function activate(User $user, Course $course, ?int $accessDays = null): Enrollment
    {
        $enrollment = Enrollment::withTrashed()->firstOrNew([
            'user_id' => $user->id,
            'course_id' => $course->id,
        ]);

        if ($enrollment->exists && $enrollment->trashed()) {
            $enrollment->restore();
        }

        $enrollment->forceFill([
            'status' => EnrollmentStatus::Active,
            'progress_percentage' => $enrollment->progress_percentage ?? 0,
            'enrolled_at' => $enrollment->enrolled_at ?? now(),
            'completed_at' => null,
            'last_accessed_at' => now(),
            'expires_at' => $accessDays === null ? null : now()->addDays($accessDays),
        ])->save();

        $this->courseService->syncStatistics($course);

        return $enrollment->fresh(['user', 'course.category', 'course.media']);
    }

    public function enroll(User $user, Course $course): Enrollment
    {
        return $this->activate($user, $course);
    }

    public function mine(User $user, int $perPage = 20): LengthAwarePaginator
    {
        return Enrollment::query()
            ->where('user_id', $user->id)
            ->with(['course.category', 'course.media'])
            ->latest('last_accessed_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function showMine(User $user, Enrollment $enrollment): Enrollment
    {
        abort_unless($enrollment->user_id === $user->id, 404);

        return $enrollment->load(['user', 'course.category', 'course.media']);
    }

    public function activeEnrollment(User $user, Course $course): Enrollment
    {
        $enrollment = Enrollment::query()
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->firstOrFail();

        abort_if($enrollment->isExpired(), 403, __('Course access has expired.'));

        return $enrollment;
    }

    public function syncProgress(Enrollment $enrollment): Enrollment
    {
        return $this->courseProgressCalculator->syncEnrollment($enrollment);
    }
}
