<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Report;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\User;
use App\Services\Report\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ReportController extends Controller
{
    public function __construct(
        private readonly ReportService $reportService,
    ) {}

    public function learningOverview(Request $request): JsonResponse
    {
        return response()->json($this->reportService->learningOverview());
    }

    public function course(Course $course): JsonResponse
    {
        return response()->json($this->reportService->course($course));
    }

    public function userProgress(User $user): JsonResponse
    {
        return response()->json($this->reportService->userProgress($user));
    }

    public function assessment(Assessment $assessment): JsonResponse
    {
        return response()->json($this->reportService->assessment($assessment));
    }

    public function exportLearning(): JsonResponse
    {
        return response()->json($this->reportService->exportLearning());
    }
}
