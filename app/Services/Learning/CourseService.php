<?php

declare(strict_types=1);

namespace App\Services\Learning;

use App\Data\Learning\CourseData;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\CourseModule;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\CourseReview;
use App\Support\Slugger;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;

final class CourseService
{
    public function __construct(
        private readonly CourseProgressCalculator $courseProgressCalculator,
    ) {}

    public function index(array $filters = [], int $perPage = 20, ?string $search = null, bool $catalog = false): LengthAwarePaginator
    {
        $query = QueryBuilder::for(
            Course::query()
                ->with(['category', 'instructor', 'media'])
                ->when($catalog, static fn ($builder) => $builder
                    ->where('status', 'published')
                    ->where('visibility', 'public'))
        )
            ->allowedFilters(
                AllowedFilter::exact('categoryId', 'category_id'),
                AllowedFilter::exact('instructorId', 'instructor_id'),
                AllowedFilter::exact('level'),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('visibility'),
                AllowedFilter::callback('publishedAfter', static fn ($query, $value) => $query->whereDate('published_at', '>=', $value)),
                AllowedFilter::callback('publishedBefore', static fn ($query, $value) => $query->whereDate('published_at', '<=', $value)),
            )
            ->allowedSorts(
                AllowedSort::field('title'),
                AllowedSort::field('created_at'),
                AllowedSort::field('published_at'),
                AllowedSort::field('duration_minutes'),
                AllowedSort::field('average_rating'),
            );

        if ($search !== null && $search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('title', 'like', '%'.$search.'%')
                    ->orWhere('short_description', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function catalogShow(Course $course): Course
    {
        return $course->load([
            'category',
            'instructor',
            'modules' => static fn ($query) => $query
                ->where('is_active', true)
                ->orderBy('position')
                ->with([
                    'lessons' => static fn ($lessonQuery) => $lessonQuery
                        ->where('is_active', true)
                        ->orderBy('position')
                        ->with(['courseModule', 'assessment.questions.options', 'media']),
                ]),
            'media',
        ]);
    }

    public function show(Course $course): Course
    {
        return $course->load([
            'category',
            'instructor',
            'modules.lessons.courseModule',
            'modules.lessons.assessment.questions.options',
            'modules.lessons.media',
            'assessments.questions.options',
            'reviews',
            'media',
        ]);
    }

    public function store(CourseData $data): Course
    {
        $course = new Course();
        $course->fill([
            'category_id' => $data->categoryId,
            'instructor_id' => $data->instructorId,
            'title' => $data->title,
            'slug' => Slugger::unique(Course::class, $data->title),
            'short_description' => $data->shortDescription,
            'description' => $data->description,
            'level' => $data->level,
            'language' => $data->language ?? 'en',
            'status' => $data->status ?? 'draft',
            'visibility' => $data->visibility ?? 'public',
            'published_at' => $data->publishedAt,
        ]);
        $course->save();

        return $this->syncStatistics($course);
    }

    public function update(Course $course, CourseData $data): Course
    {
        $course->fill([
            'category_id' => $data->categoryId,
            'instructor_id' => $data->instructorId,
            'title' => $data->title,
            'slug' => Slugger::unique(Course::class, $data->title, $course->id),
            'short_description' => $data->shortDescription,
            'description' => $data->description,
            'level' => $data->level,
            'language' => $data->language ?? 'en',
            'status' => $data->status ?? $course->status,
            'visibility' => $data->visibility ?? $course->visibility,
            'published_at' => $data->publishedAt ?? $course->published_at,
        ]);
        $course->save();

        return $this->syncStatistics($course);
    }

    public function publish(Course $course): Course
    {
        $course->forceFill([
            'status' => 'published',
            'published_at' => $course->published_at ?? now(),
        ])->save();

        return $this->syncStatistics($course);
    }

    public function archive(Course $course): Course
    {
        $course->forceFill(['status' => 'archived'])->save();

        return $course->fresh(['category', 'instructor', 'media']);
    }

    public function delete(Course $course): void
    {
        $course->delete();
    }

    public function media(Course $course, array $files = []): Course
    {
        if (isset($files['cover'])) {
            $course->clearMediaCollection('cover');
            $course->addMedia($files['cover'])->toMediaCollection('cover');
        }

        if (isset($files['introVideo'])) {
            $course->clearMediaCollection('intro_video');
            $course->addMedia($files['introVideo'])->toMediaCollection('intro_video');
        }

        foreach ($files['attachments'] ?? [] as $attachment) {
            $course->addMedia($attachment)->toMediaCollection('attachments');
        }

        return $course->fresh(['category', 'instructor', 'media']);
    }

    public function destroyMedia(Course $course, Media $media): Course
    {
        $media->delete();

        return $course->fresh(['category', 'instructor', 'media']);
    }

    public function syncStatistics(Course $course): Course
    {
        $course->loadCount([
            'lessons',
            'reviews as reviews_count' => static fn ($query) => $query->where('is_visible', true),
        ]);

        $durationSeconds = (int) $course->lessons()
            ->where('is_active', true)
            ->sum('duration_seconds');

        $course->forceFill([
            'lessons_count' => $course->lessons()->where('is_active', true)->count(),
            'duration_minutes' => (int) ceil($durationSeconds / 60),
            'students_count' => Enrollment::query()->where('course_id', $course->id)->where('status', 'active')->count(),
            'ratings_count' => CourseReview::query()->where('course_id', $course->id)->where('is_visible', true)->count(),
            'average_rating' => CourseReview::query()->where('course_id', $course->id)->where('is_visible', true)->avg('rating') ?? 0,
        ])->save();

        return $course->fresh(['category', 'instructor', 'media']);
    }
}
