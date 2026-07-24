<?php

declare(strict_types=1);

namespace App\Services\Learning;

use App\Http\Resources\Api\V1\Learning\CourseResource;
use App\Http\Resources\Api\V1\Learning\EnrollmentResource;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LessonProgress;
use App\Models\User;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

final class StudentLearningService
{
    public function __construct(
        private readonly EnrollmentService $enrollmentService,
    ) {}

    public function course(User $user, Course $course): array
    {
        $enrollment = $this->enrollmentService->activeEnrollment($user, $course);

        $course->load([
            'category.media',
            'instructor.media',
            'media',
            'modules' => static fn ($query) => $query
                ->where('is_active', true)
                ->orderBy('position')
                ->with([
                    'lessons' => static fn ($lessonQuery) => $lessonQuery
                        ->where('is_active', true)
                        ->orderBy('position')
                        ->with(['media', 'assessment']),
                ]),
        ]);

        $progress = LessonProgress::query()
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->get()
            ->keyBy('lesson_id');

        $videoCount = 0;
        $fileCount = 0;
        $completedLessonsCount = 0;
        $resumeLessonId = null;

        $modules = $course->modules->map(function ($module) use (
            $progress,
            &$videoCount,
            &$fileCount,
            &$completedLessonsCount,
            &$resumeLessonId,
        ): array {
            return [
                'id' => $module->id,
                'courseId' => $module->course_id,
                'title' => $module->title,
                'description' => $module->description,
                'position' => (int) $module->position,
                'lessons' => $module->lessons->map(function ($lesson) use (
                    $progress,
                    &$videoCount,
                    &$fileCount,
                    &$completedLessonsCount,
                    &$resumeLessonId,
                ): array {
                    $lessonProgress = $progress->get($lesson->id);
                    $status = $lessonProgress?->status?->value ?? $lessonProgress?->status ?? 'not_started';
                    $progressSeconds = (int) ($lessonProgress?->progress_seconds ?? 0);
                    $duration = (int) $lesson->duration_seconds;
                    $percentage = $duration > 0
                        ? round(min(100, ($progressSeconds / $duration) * 100), 2)
                        : 0;
                    $video = $lesson->media->firstWhere('collection_name', 'video');
                    $thumbnail = $lesson->media->firstWhere('collection_name', 'images');
                    $files = $lesson->media->where('collection_name', 'files')->values();

                    if ($video !== null) {
                        $videoCount++;
                    }
                    $fileCount += $files->count();
                    if ($status === 'completed') {
                        $completedLessonsCount++;
                    } elseif ($resumeLessonId === null && $progressSeconds > 0) {
                        $resumeLessonId = $lesson->id;
                    }

                    return [
                        'id' => $lesson->id,
                        'courseId' => $lesson->course_id,
                        'courseModuleId' => $lesson->course_module_id,
                        'title' => $lesson->title,
                        'body' => $lesson->body,
                        'type' => $lesson->type?->value ?? $lesson->type,
                        'durationSeconds' => $duration,
                        'position' => (int) $lesson->position,
                        'isPreview' => (bool) $lesson->is_preview,
                        'canAccess' => true,
                        'progress' => [
                            'status' => $status,
                            'progressSeconds' => $progressSeconds,
                            'progressPercentage' => $percentage,
                            'completedAt' => $lessonProgress?->completed_at?->toISOString(),
                            'lastAccessedAt' => $lessonProgress?->last_accessed_at?->toISOString(),
                        ],
                        'video' => $video ? $this->media($video, $thumbnail?->getUrl()) : null,
                        'files' => $files->map(fn (Media $media): array => $this->media($media))->all(),
                        'assessment' => $lesson->relationLoaded('assessment') && $lesson->assessment
                            ? [
                                'id' => $lesson->assessment->id,
                                'title' => $lesson->assessment->title,
                                'type' => $lesson->assessment->type?->value ?? $lesson->assessment->type,
                                'status' => $lesson->assessment->status?->value ?? $lesson->assessment->status,
                            ]
                            : null,
                    ];
                })->all(),
            ];
        })->all();

        if ($resumeLessonId === null) {
            $resumeLessonId = $course->modules
                ->flatMap(fn ($module) => $module->lessons)
                ->first(fn ($lesson) => ($progress->get($lesson->id)?->status?->value ?? $progress->get($lesson->id)?->status) !== 'completed')
                ?->id;
        }

        $courseForResource = clone $course;
        $courseForResource->unsetRelation('modules');

        return [
            'course' => CourseResource::make($courseForResource)->resolve(),
            'enrollment' => EnrollmentResource::make($enrollment)->resolve(),
            'summary' => [
                'videoCount' => $videoCount,
                'fileCount' => $fileCount,
                'completedLessonsCount' => $completedLessonsCount,
                'resumeLessonId' => $resumeLessonId,
            ],
            'modules' => $modules,
        ];
    }

    public function summary(User $user): array
    {
        $enrollments = Enrollment::query()->where('user_id', $user->id)->get();
        $learningSeconds = (int) LessonProgress::query()
            ->where('user_id', $user->id)
            ->sum('progress_seconds');

        return [
            'coursesCount' => $enrollments->count(),
            'activeCoursesCount' => $enrollments->filter(fn (Enrollment $enrollment) => ! $enrollment->isExpired() && ($enrollment->status?->value ?? $enrollment->status) === 'active')->count(),
            'completedCoursesCount' => $enrollments->filter(fn (Enrollment $enrollment) => ($enrollment->status?->value ?? $enrollment->status) === 'completed')->count(),
            'completedLessonsCount' => LessonProgress::query()
                ->where('user_id', $user->id)
                ->where('status', 'completed')
                ->count(),
            'learningMinutes' => (int) ceil($learningSeconds / 60),
        ];
    }

    private function media(Media $media, ?string $thumbnailUrl = null): array
    {
        return [
            'id' => $media->id,
            'name' => $media->name,
            'fileName' => $media->file_name,
            'mimeType' => $media->mime_type,
            'url' => $media->getUrl(),
            'thumbnailUrl' => $thumbnailUrl,
            'size' => $media->size,
        ];
    }
}
