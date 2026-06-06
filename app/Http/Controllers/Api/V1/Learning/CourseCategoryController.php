<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Learning;

use App\Data\Learning\CourseCategoryData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Learning\CourseCategoryRequest;
use App\Http\Requests\Api\V1\Learning\CourseCategoryIndexRequest;
use App\Http\Resources\Api\V1\Learning\CourseCategoryResource;
use App\Models\CourseCategory;
use App\Services\Learning\CourseCategoryService;
use Illuminate\Http\JsonResponse;

final class CourseCategoryController extends Controller
{
    public function __construct(
        private readonly CourseCategoryService $courseCategoryService,
    ) {}

    public function index(CourseCategoryIndexRequest $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        return CourseCategoryResource::collection(
            $this->courseCategoryService->index(
                $request->validated(),
                (int) $request->integer('perPage', 20),
                $request->validated('search')
            )
        );
    }

    public function store(CourseCategoryRequest $request): CourseCategoryResource
    {
        return CourseCategoryResource::make(
            $this->courseCategoryService->store(CourseCategoryData::from($request->validated()))
        );
    }

    public function show(CourseCategory $course_category): CourseCategoryResource
    {
        return CourseCategoryResource::make(
            $course_category->load(['parent', 'children'])->loadCount('courses')
        );
    }

    public function update(CourseCategoryRequest $request, CourseCategory $course_category): CourseCategoryResource
    {
        return CourseCategoryResource::make(
            $this->courseCategoryService->update($course_category, CourseCategoryData::from($request->validated()))
        );
    }

    public function destroy(CourseCategory $course_category): JsonResponse
    {
        $this->courseCategoryService->delete($course_category);

        return response()->json(null, 204);
    }
}
