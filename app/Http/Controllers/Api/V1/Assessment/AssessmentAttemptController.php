<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Assessment;

use App\Data\Assessment\AttemptAnswerData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Assessment\AttemptAnswerRequest;
use App\Http\Resources\Api\V1\Assessment\AssessmentAttemptResource;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Services\Assessment\AssessmentAttemptService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AssessmentAttemptController extends Controller
{
    public function __construct(
        private readonly AssessmentAttemptService $assessmentAttemptService,
    ) {}

    public function index(Request $request, Assessment $assessment): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        return AssessmentAttemptResource::collection(
            $this->assessmentAttemptService->index(
                $assessment,
                (int) $request->integer('perPage', 20)
            )
        );
    }

    public function start(Request $request, Assessment $assessment): AssessmentAttemptResource
    {
        return AssessmentAttemptResource::make(
            $this->assessmentAttemptService->start($assessment, $request->user())
        );
    }

    public function show(AssessmentAttempt $assessmentAttempt): AssessmentAttemptResource
    {
        return AssessmentAttemptResource::make(
            $this->assessmentAttemptService->show($assessmentAttempt)
        );
    }

    public function saveAnswer(AttemptAnswerRequest $request, AssessmentAttempt $assessmentAttempt): \App\Http\Resources\Api\V1\Assessment\AttemptAnswerResource
    {
        return \App\Http\Resources\Api\V1\Assessment\AttemptAnswerResource::make(
            $this->assessmentAttemptService->saveAnswer(
                $assessmentAttempt,
                AttemptAnswerData::from($request->validated())
            )
        );
    }

    public function submit(AssessmentAttempt $assessmentAttempt): AssessmentAttemptResource
    {
        return AssessmentAttemptResource::make(
            $this->assessmentAttemptService->submit($assessmentAttempt)
        );
    }

    public function result(AssessmentAttempt $assessmentAttempt): AssessmentAttemptResource
    {
        return AssessmentAttemptResource::make(
            $this->assessmentAttemptService->result($assessmentAttempt)
        );
    }

    public function grade(AssessmentAttempt $assessmentAttempt): AssessmentAttemptResource
    {
        return AssessmentAttemptResource::make(
            $this->assessmentAttemptService->grade($assessmentAttempt)
        );
    }
}
