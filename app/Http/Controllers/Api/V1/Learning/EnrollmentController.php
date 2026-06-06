<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Learning;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Learning\EnrollmentResource;
use App\Models\Course;
use App\Models\Enrollment;
use App\Services\Learning\EnrollmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class EnrollmentController extends Controller
{
    public function __construct(
        private readonly EnrollmentService $enrollmentService,
    ) {}

    public function store(Request $request, Course $course): EnrollmentResource
    {
        return EnrollmentResource::make($this->enrollmentService->enroll($request->user(), $course));
    }

    public function mine(Request $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        return EnrollmentResource::collection(
            $this->enrollmentService->mine(
                $request->user(),
                (int) $request->integer('perPage', 20)
            )
        );
    }

    public function showMine(Request $request, Enrollment $enrollment): EnrollmentResource
    {
        return EnrollmentResource::make(
            $this->enrollmentService->showMine($request->user(), $enrollment)
        );
    }
}
