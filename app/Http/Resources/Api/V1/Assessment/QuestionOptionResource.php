<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1\Assessment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class QuestionOptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'questionId' => $this->question_id,
            'optionText' => $this->option_text,
            'isCorrect' => (bool) $this->is_correct,
            'position' => (int) $this->position,
            'question' => $this->relationLoaded('question') ? QuestionResource::make($this->question) : null,
            'createdAt' => $this->created_at?->toISOString(),
            'updatedAt' => $this->updated_at?->toISOString(),
        ];
    }
}
