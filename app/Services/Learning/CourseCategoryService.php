<?php

declare(strict_types=1);

namespace App\Services\Learning;

use App\Data\Learning\CourseCategoryData;
use App\Enums\Learning\CourseStatus;
use App\Models\CourseCategory;
use App\Support\Slugger;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;

final class CourseCategoryService
{
    public function index(array $filters = [], int $perPage = 20, ?string $search = null): LengthAwarePaginator
    {
        $query = QueryBuilder::for(CourseCategory::query())
            ->with(['parent'])
            ->withCount('courses')
            ->allowedFilters(
                AllowedFilter::exact('parentId', 'parent_id'),
                AllowedFilter::exact('isActive', 'is_active'),
                AllowedFilter::exact('slug'),
            )
            ->allowedSorts(
                AllowedSort::field('name'),
                AllowedSort::field('sort_order', 'sort_order'),
                AllowedSort::field('created_at'),
            );

        if ($search !== null && $search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('name', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function tree(): Collection
    {
        return CourseCategory::query()
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->withCount('courses')
            ->with([
                'children' => static fn ($query) => $query
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->withCount('courses')
                    ->with([
                        'children' => static fn ($childQuery) => $childQuery
                            ->where('is_active', true)
                            ->orderBy('sort_order'),
                    ]),
            ])
            ->orderBy('sort_order')
            ->get();
    }

    public function store(CourseCategoryData $data): CourseCategory
    {
        $category = new CourseCategory();
        $category->fill([
            'parent_id' => $data->parentId,
            'name' => $data->name,
            'slug' => Slugger::unique(CourseCategory::class, $data->name),
            'description' => $data->description,
            'icon' => $data->icon,
            'color' => $data->color,
            'is_active' => $data->isActive,
            'sort_order' => $data->sortOrder,
        ]);
        $category->save();

        return $category->fresh(['parent', 'children'])->loadCount('courses');
    }

    public function update(CourseCategory $category, CourseCategoryData $data): CourseCategory
    {
        $category->fill([
            'parent_id' => $data->parentId,
            'name' => $data->name,
            'slug' => Slugger::unique(CourseCategory::class, $data->name, $category->id),
            'description' => $data->description,
            'icon' => $data->icon,
            'color' => $data->color,
            'is_active' => $data->isActive,
            'sort_order' => $data->sortOrder,
        ]);
        $category->save();

        return $category->fresh(['parent', 'children'])->loadCount('courses');
    }

    public function delete(CourseCategory $category): void
    {
        $category->delete();
    }
}
