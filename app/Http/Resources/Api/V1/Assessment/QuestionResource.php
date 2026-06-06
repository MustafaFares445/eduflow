<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1\Assessment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class QuestionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'assessmentId' => $this->assessment_id,
            'type' => $this->type?->value ?? $this->type,
            'questionText' => $this->question_text,
            'explanation' => $this->explanation,
            'points' => (int) $this->points,
            'position' => (int) $this->position,
            'isActive' => (bool) $this->is_active,
            'assessment' => $this->relationLoaded('assessment') ? AssessmentResource::make($this->assessment) : null,
            'options' => $this->relationLoaded('options') ? QuestionOptionResource::collection($this->options) : null,
            'createdAt' => $this->created_at?->toISOString(),
            'updatedAt' => $this->updated_at?->toISOString(),
        ];
    }
}
