<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1\Assessment;

use App\Http\Resources\Api\V1\Learning\CourseResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class AssessmentAttemptResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'assessmentId' => $this->assessment_id,
            'userId' => $this->user_id,
            'status' => $this->status?->value ?? $this->status,
            'startedAt' => $this->started_at?->toISOString(),
            'submittedAt' => $this->submitted_at?->toISOString(),
            'expiresAt' => $this->expires_at?->toISOString(),
            'score' => $this->score,
            'maxScore' => $this->max_score,
            'percentage' => $this->percentage,
            'isPassed' => (bool) $this->is_passed,
            'attemptNumber' => (int) $this->attempt_number,
            'gradedAt' => $this->graded_at?->toISOString(),
            'assessment' => $this->relationLoaded('assessment') ? AssessmentResource::make($this->assessment) : null,
            'user' => $this->relationLoaded('user') ? \App\Http\Resources\Api\V1\UserResource::make($this->user) : null,
            'answers' => $this->relationLoaded('answers') ? AttemptAnswerResource::collection($this->answers) : null,
            'createdAt' => $this->created_at?->toISOString(),
            'updatedAt' => $this->updated_at?->toISOString(),
        ];
    }
}
