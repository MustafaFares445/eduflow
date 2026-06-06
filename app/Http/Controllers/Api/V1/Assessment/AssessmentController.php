<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Assessment;

use App\Data\Assessment\AssessmentData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Assessment\AssessmentRequest;
use App\Http\Resources\Api\V1\Assessment\AssessmentResource;
use App\Models\Assessment;
use App\Services\Assessment\AssessmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AssessmentController extends Controller
{
    public function __construct(
        private readonly AssessmentService $assessmentService,
    ) {}

    public function index(Request $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        return AssessmentResource::collection(
            $this->assessmentService->index(
                $request->all(),
                (int) $request->integer('perPage', 20),
                $request->string('search')->toString() ?: null
            )
        );
    }

    public function store(AssessmentRequest $request): AssessmentResource
    {
        return AssessmentResource::make(
            $this->assessmentService->store(AssessmentData::from($request->validated()))
        );
    }

    public function show(Assessment $assessment): AssessmentResource
    {
        return AssessmentResource::make($this->assessmentService->show($assessment));
    }

    public function update(AssessmentRequest $request, Assessment $assessment): AssessmentResource
    {
        return AssessmentResource::make(
            $this->assessmentService->update($assessment, AssessmentData::from($request->validated()))
        );
    }

    public function destroy(Assessment $assessment): JsonResponse
    {
        $this->assessmentService->delete($assessment);

        return response()->noContent();
    }

    public function publish(Assessment $assessment): AssessmentResource
    {
        return AssessmentResource::make($this->assessmentService->publish($assessment));
    }

    public function archive(Assessment $assessment): AssessmentResource
    {
        return AssessmentResource::make($this->assessmentService->archive($assessment));
    }
}
