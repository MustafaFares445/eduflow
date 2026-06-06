<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Learning;

use App\Data\Learning\CourseModuleData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Learning\CourseModuleReorderRequest;
use App\Http\Requests\Api\V1\Learning\CourseModuleRequest;
use App\Http\Resources\Api\V1\Learning\CourseModuleResource;
use App\Models\CourseModule;
use App\Services\Learning\CourseModuleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CourseModuleController extends Controller
{
    public function __construct(
        private readonly CourseModuleService $courseModuleService,
    ) {}

    public function index(Request $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        return CourseModuleResource::collection(
            $this->courseModuleService->index(
                $request->all(),
                (int) $request->integer('perPage', 20),
                $request->string('search')->toString() ?: null
            )
        );
    }

    public function store(CourseModuleRequest $request): CourseModuleResource
    {
        return CourseModuleResource::make(
            $this->courseModuleService->store(CourseModuleData::from($request->validated()))
        );
    }

    public function show(CourseModule $course_module): CourseModuleResource
    {
        return CourseModuleResource::make($course_module->load(['course.category', 'lessons']));
    }

    public function update(CourseModuleRequest $request, CourseModule $course_module): CourseModuleResource
    {
        return CourseModuleResource::make(
            $this->courseModuleService->update($course_module, CourseModuleData::from($request->validated()))
        );
    }

    public function destroy(CourseModule $course_module): JsonResponse
    {
        $this->courseModuleService->delete($course_module);

        return response()->json(null, 204);
    }

    public function reorder(CourseModuleReorderRequest $request): JsonResponse
    {
        $this->courseModuleService->reorder($request->validated('items'));

        return response()->json([
            'message' => __('Course modules reordered successfully.'),
        ]);
    }
}
