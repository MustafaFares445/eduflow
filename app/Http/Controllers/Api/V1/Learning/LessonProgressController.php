<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Learning;

use App\Data\Learning\LessonProgressData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Learning\LessonProgressRequest;
use App\Http\Resources\Api\V1\Learning\LessonProgressResource;
use App\Models\Lesson;
use App\Services\Learning\LessonProgressService;
use Illuminate\Http\Request;

final class LessonProgressController extends Controller
{
    public function __construct(
        private readonly LessonProgressService $lessonProgressService,
    ) {}

    public function update(LessonProgressRequest $request, Lesson $lesson): LessonProgressResource
    {
        return LessonProgressResource::make(
            $this->lessonProgressService->update(
                $request->user(),
                $lesson,
                LessonProgressData::from($request->validated())
            )
        );
    }

    public function complete(Request $request, Lesson $lesson): LessonProgressResource
    {
        return LessonProgressResource::make(
            $this->lessonProgressService->complete($request->user(), $lesson)
        );
    }
}
