<?php

declare(strict_types=1);

namespace App\Services\Learning;

use App\Data\Learning\LessonData;
use App\Models\Course;
use App\Models\Lesson;
use App\Support\Slugger;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;

final class LessonService
{
    public function __construct(
        private readonly CourseService $courseService,
    ) {}

    public function index(array $filters = [], int $perPage = 20, ?string $search = null): LengthAwarePaginator
    {
        $query = QueryBuilder::for(Lesson::query())
            ->with(['course.category', 'courseModule', 'media'])
            ->allowedFilters(
                AllowedFilter::exact('courseId', 'course_id'),
                AllowedFilter::exact('courseModuleId', 'course_module_id'),
                AllowedFilter::exact('type'),
                AllowedFilter::exact('isPreview', 'is_preview'),
                AllowedFilter::exact('isActive', 'is_active'),
            )
            ->allowedSorts(
                AllowedSort::field('title'),
                AllowedSort::field('position'),
                AllowedSort::field('created_at'),
                AllowedSort::field('published_at'),
            );

        if ($search !== null && $search !== '') {
            $query->where('title', 'like', '%'.$search.'%');
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function catalogShow(Lesson $lesson): Lesson
    {
        return $lesson->load([
            'course.category',
            'courseModule',
            'assessment.questions.options',
            'media',
        ]);
    }

    public function show(Lesson $lesson): Lesson
    {
        return $lesson->load([
            'course.category',
            'courseModule',
            'assessment.questions.options',
            'media',
        ]);
    }

    public function store(LessonData $data): Lesson
    {
        $lesson = new Lesson();
        $lesson->fill([
            'course_id' => $data->courseId,
            'course_module_id' => $data->courseModuleId,
            'title' => $data->title,
            'slug' => Slugger::unique(Lesson::class, $data->title),
            'type' => $data->type,
            'body' => $data->body,
            'duration_seconds' => $data->durationSeconds,
            'position' => $data->position,
            'is_preview' => $data->isPreview,
            'is_active' => $data->isActive,
            'published_at' => $data->publishedAt,
        ]);
        $lesson->save();

        $this->courseService->syncStatistics($lesson->course()->firstOrFail());

        return $lesson->fresh(['course.category', 'courseModule', 'media']);
    }

    public function update(Lesson $lesson, LessonData $data): Lesson
    {
        $lesson->fill([
            'course_id' => $data->courseId,
            'course_module_id' => $data->courseModuleId,
            'title' => $data->title,
            'slug' => Slugger::unique(Lesson::class, $data->title, $lesson->id),
            'type' => $data->type,
            'body' => $data->body,
            'duration_seconds' => $data->durationSeconds,
            'position' => $data->position,
            'is_preview' => $data->isPreview,
            'is_active' => $data->isActive,
            'published_at' => $data->publishedAt ?? $lesson->published_at,
        ]);
        $lesson->save();

        $this->courseService->syncStatistics($lesson->course()->firstOrFail());

        return $lesson->fresh(['course.category', 'courseModule', 'media']);
    }

    public function reorder(array $items): void
    {
        DB::transaction(function () use ($items): void {
            foreach ($items as $item) {
                Lesson::query()
                    ->whereKey($item['id'])
                    ->update(['position' => $item['position']]);
            }
        });
    }

    public function media(Lesson $lesson, array $files = []): Lesson
    {
        if (isset($files['video'])) {
            $lesson->clearMediaCollection('video');
            $lesson->addMedia($files['video'])->toMediaCollection('video');
        }

        foreach ($files['files'] ?? [] as $file) {
            $lesson->addMedia($file)->toMediaCollection('files');
        }

        foreach ($files['images'] ?? [] as $image) {
            $lesson->addMedia($image)->toMediaCollection('images');
        }

        return $lesson->fresh(['media']);
    }

    public function destroyMedia(Lesson $lesson, Media $media): Lesson
    {
        $media->delete();

        return $lesson->fresh(['media']);
    }

    public function delete(Lesson $lesson): void
    {
        $course = $lesson->course()->firstOrFail();
        $lesson->delete();

        $this->courseService->syncStatistics($course);
    }
}
