<?php

declare(strict_types=1);

namespace App\Services\Learning;

use App\Data\Learning\CourseModuleData;
use App\Models\Course;
use App\Models\CourseModule;
use App\Support\Slugger;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;

final class CourseModuleService
{
    public function index(array $filters = [], int $perPage = 20, ?string $search = null): LengthAwarePaginator
    {
        $query = QueryBuilder::for(CourseModule::query())
            ->with(['course.category'])
            ->allowedFilters(
                AllowedFilter::exact('courseId', 'course_id'),
                AllowedFilter::exact('isActive', 'is_active'),
            )
            ->allowedSorts(
                AllowedSort::field('title'),
                AllowedSort::field('position'),
                AllowedSort::field('created_at'),
            );

        if ($search !== null && $search !== '') {
            $query->where('title', 'like', '%'.$search.'%');
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function catalogByCourse(Course $course): Course
    {
        return $course->load([
            'modules' => static fn ($query) => $query->where('is_active', true)->orderBy('position')->with([
                'lessons' => static fn ($lessonQuery) => $lessonQuery->where('is_active', true)->orderBy('position'),
            ]),
        ]);
    }

    public function store(CourseModuleData $data): CourseModule
    {
        $module = new CourseModule();
        $module->fill([
            'course_id' => $data->courseId,
            'title' => $data->title,
            'description' => $data->description,
            'position' => $data->position,
            'is_active' => $data->isActive,
        ]);
        $module->save();

        return $module->fresh(['course.category', 'lessons']);
    }

    public function update(CourseModule $module, CourseModuleData $data): CourseModule
    {
        $module->fill([
            'course_id' => $data->courseId,
            'title' => $data->title,
            'description' => $data->description,
            'position' => $data->position,
            'is_active' => $data->isActive,
        ]);
        $module->save();

        return $module->fresh(['course.category', 'lessons']);
    }

    public function reorder(array $items): void
    {
        DB::transaction(function () use ($items): void {
            foreach ($items as $item) {
                CourseModule::query()
                    ->whereKey($item['id'])
                    ->update(['position' => $item['position']]);
            }
        });
    }

    public function delete(CourseModule $module): void
    {
        $module->delete();
    }
}
