<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1\Assessment;

use App\Http\Resources\Api\V1\Learning\CourseResource;
use App\Http\Resources\Api\V1\Learning\LessonResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class AssessmentResource extends JsonResource
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
            'shuffleQuestions' => (bool) $this->shuffle_questions,
            'showResultImmediately' => (bool) $this->show_result_immediately,
            'position' => (int) $this->position,
            'course' => $this->relationLoaded('course') ? CourseResource::make($this->course) : null,
            'lesson' => $this->relationLoaded('lesson') ? LessonResource::make($this->lesson) : null,
            'questions' => $this->relationLoaded('questions') ? QuestionResource::collection($this->questions) : null,
            'attempts' => $this->relationLoaded('attempts') ? AssessmentAttemptResource::collection($this->attempts) : null,
            'createdAt' => $this->created_at?->toISOString(),
            'updatedAt' => $this->updated_at?->toISOString(),
        ];
    }
}
