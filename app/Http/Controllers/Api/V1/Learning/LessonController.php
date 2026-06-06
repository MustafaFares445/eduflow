<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Learning;

use App\Data\Learning\LessonData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Learning\LessonFilterRequest;
use App\Http\Requests\Api\V1\Learning\LessonMediaRequest;
use App\Http\Requests\Api\V1\Learning\LessonReorderRequest;
use App\Http\Requests\Api\V1\Learning\LessonRequest;
use App\Http\Resources\Api\V1\Learning\LessonResource;
use App\Models\Lesson;
use App\Services\Learning\LessonService;
use Illuminate\Http\JsonResponse;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

final class LessonController extends Controller
{
    public function __construct(
        private readonly LessonService $lessonService,
    ) {}

    public function index(LessonFilterRequest $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        return LessonResource::collection(
            $this->lessonService->index(
                $request->validated(),
                (int) $request->integer('perPage', 20),
                $request->validated('search')
            )
        );
    }

    public function store(LessonRequest $request): LessonResource
    {
        return LessonResource::make(
            $this->lessonService->store(LessonData::from($request->validated()))
        );
    }

    public function show(Lesson $lesson): LessonResource
    {
        return LessonResource::make($this->lessonService->show($lesson));
    }

    public function update(LessonRequest $request, Lesson $lesson): LessonResource
    {
        return LessonResource::make(
            $this->lessonService->update($lesson, LessonData::from($request->validated()))
        );
    }

    public function destroy(Lesson $lesson): JsonResponse
    {
        $this->lessonService->delete($lesson);

        return response()->json(null, 204);
    }

    public function reorder(LessonReorderRequest $request): JsonResponse
    {
        $this->lessonService->reorder($request->validated('items'));

        return response()->json([
            'message' => __('Lessons reordered successfully.'),
        ]);
    }

    public function media(LessonMediaRequest $request, Lesson $lesson): LessonResource
    {
        return LessonResource::make(
            $this->lessonService->media($lesson, $request->allFiles())
        );
    }

    public function mediaDestroy(Lesson $lesson, Media $media): LessonResource
    {
        return LessonResource::make($this->lessonService->destroyMedia($lesson, $media));
    }
}
