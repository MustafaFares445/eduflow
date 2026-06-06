<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Learning\CourseCategoryIndexRequest;
use App\Http\Requests\Api\V1\Learning\CourseFilterRequest;
use App\Http\Requests\Api\V1\Learning\LessonFilterRequest;
use App\Http\Resources\Api\V1\Learning\CourseCategoryResource;
use App\Http\Resources\Api\V1\Learning\CourseModuleResource;
use App\Http\Resources\Api\V1\Learning\CourseResource;
use App\Http\Resources\Api\V1\Learning\LessonResource;
use App\Models\Course;
use App\Models\Lesson;
use App\Services\Learning\CourseCategoryService;
use App\Services\Learning\CourseModuleService;
use App\Services\Learning\CourseService;
use App\Services\Learning\LessonService;
use Illuminate\Http\Request;

final class CatalogController extends Controller
{
    public function __construct(
        private readonly CourseCategoryService $courseCategoryService,
        private readonly CourseService $courseService,
        private readonly CourseModuleService $courseModuleService,
        private readonly LessonService $lessonService,
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
        return CourseResource::make($this->courseService->catalogShow($course));
    }

    public function modules(Course $course): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        return CourseModuleResource::collection(
            $this->courseModuleService->catalogByCourse($course)->modules
        );
    }

    public function lesson(Lesson $lesson): LessonResource
    {
        return LessonResource::make($this->lessonService->catalogShow($lesson));
    }
}
