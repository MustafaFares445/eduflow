<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Learning\CourseCategoryIndexRequest;
use App\Http\Requests\Api\V1\Learning\CourseFilterRequest;
use App\Http\Resources\Api\V1\Learning\CourseCategoryResource;
use App\Http\Resources\Api\V1\Learning\CourseResource;
use App\Models\Course;
use App\Services\Learning\CourseCategoryService;
use App\Services\Learning\CourseService;

final class CatalogController extends Controller
{
    public function __construct(
        private readonly CourseCategoryService $courseCategoryService,
        private readonly CourseService $courseService,
    ) {}

    public function categories(CourseCategoryIndexRequest $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        if ($request->has('search') || $request->has('perPage') || $request->has('filter') || $request->has('sort')) {
            return CourseCategoryResource::collection(
                $this->courseCategoryService->index(
                    $request->validated(),
                    (int) $request->integer('perPage', 20),
                    $request->validated('search')
                )
            );
        }

        return CourseCategoryResource::collection($this->courseCategoryService->tree());
    }

    public function courses(CourseFilterRequest $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        return CourseResource::collection(
            $this->courseService->index(
                $request->validated(),
                (int) $request->integer('perPage', 20),
                $request->validated('search'),
                true
            )
        );
    }

    public function course(Course $course): CourseResource
    {
        abort_unless(
            ($course->status?->value ?? $course->status) === 'published'
            && ($course->visibility?->value ?? $course->visibility) === 'public',
            404
        );

        return CourseResource::make($course->load(['category.media', 'instructor.media', 'media']));
    }
}
