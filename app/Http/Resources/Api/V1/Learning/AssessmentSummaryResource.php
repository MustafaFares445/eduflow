<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1\Learning;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class AssessmentSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'courseId' => $this->course_id,
            'lessonId' => $this->lesson_id,
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type?->value ?? $this->type,
            'status' => $this->status?->value ?? $this->status,
            'passingScore' => (int) $this->passing_score,
            'maxAttempts' => $this->max_attempts,
            'timeLimitMinutes' => $this->time_limit_minutes,
            'position' => (int) $this->position,
        ];
    }
}
