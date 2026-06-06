<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Learning;

use App\Data\Learning\CourseData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Learning\CourseFilterRequest;
use App\Http\Requests\Api\V1\Learning\CourseMediaRequest;
use App\Http\Requests\Api\V1\Learning\CourseRequest;
use App\Http\Resources\Api\V1\Learning\CourseResource;
use App\Models\Course;
use App\Services\Learning\CourseService;
use Illuminate\Http\JsonResponse;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

final class CourseController extends Controller
{
    public function __construct(
        private readonly CourseService $courseService,
    ) {}

    public function index(CourseFilterRequest $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        return CourseResource::collection(
            $this->courseService->index(
                $request->validated(),
                (int) $request->integer('perPage', 20),
                $request->validated('search')
            )
        );
    }

    public function store(CourseRequest $request): CourseResource
    {
        return CourseResource::make(
            $this->courseService->store(CourseData::from($request->validated()))
        );
    }

    public function show(Course $course): CourseResource
    {
        return CourseResource::make($this->courseService->show($course));
    }

    public function update(CourseRequest $request, Course $course): CourseResource
    {
        return CourseResource::make(
            $this->courseService->update($course, CourseData::from($request->validated()))
        );
    }

    public function destroy(Course $course): JsonResponse
    {
        $this->courseService->delete($course);

        return response()->json(null, 204);
    }

    public function publish(Course $course): CourseResource
    {
        return CourseResource::make($this->courseService->publish($course));
    }

    public function archive(Course $course): CourseResource
    {
        return CourseResource::make($this->courseService->archive($course));
    }

    public function media(CourseMediaRequest $request, Course $course): CourseResource
    {
        return CourseResource::make(
            $this->courseService->media($course, $request->allFiles())
        );
    }

    public function mediaDestroy(Course $course, Media $media): CourseResource
    {
        return CourseResource::make($this->courseService->destroyMedia($course, $media));
    }
}
