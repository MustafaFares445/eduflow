<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Learning;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\Learning\StudentLearningService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class StudentLearningController extends Controller
{
    public function course(Request $request, Course $course, StudentLearningService $service): JsonResponse
    {
        return response()->json([
            'data' => $service->course($request->user(), $course),
        ]);
    }

    public function summary(Request $request, StudentLearningService $service): JsonResponse
    {
        return response()->json([
            'data' => $service->summary($request->user()),
        ]);
    }
}
