<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1\Assessment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class AttemptAnswerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'assessmentAttemptId' => $this->assessment_attempt_id,
            'questionId' => $this->question_id,
            'selectedOptionId' => $this->selected_option_id,
            'answerText' => $this->answer_text,
            'answerJson' => $this->answer_json,
            'isCorrect' => (bool) $this->is_correct,
            'score' => $this->score,
            'gradedAt' => $this->graded_at?->toISOString(),
            'question' => $this->relationLoaded('question') ? QuestionResource::make($this->question) : null,
            'selectedOption' => $this->relationLoaded('selectedOption') ? QuestionOptionResource::make($this->selectedOption) : null,
            'createdAt' => $this->created_at?->toISOString(),
            'updatedAt' => $this->updated_at?->toISOString(),
        ];
    }
}
