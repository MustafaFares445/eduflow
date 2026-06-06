<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1\Learning;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class LessonProgressResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'userId' => $this->user_id,
            'courseId' => $this->course_id,
            'lessonId' => $this->lesson_id,
            'status' => $this->status?->value ?? $this->status,
            'progressSeconds' => (int) $this->progress_seconds,
            'completedAt' => $this->completed_at?->toISOString(),
            'lastAccessedAt' => $this->last_accessed_at?->toISOString(),
            'user' => $this->relationLoaded('user') ? \App\Http\Resources\Api\V1\UserResource::make($this->user) : null,
            'course' => $this->relationLoaded('course') ? CourseResource::make($this->course) : null,
            'lesson' => $this->relationLoaded('lesson') ? LessonResource::make($this->lesson) : null,
            'createdAt' => $this->created_at?->toISOString(),
            'updatedAt' => $this->updated_at?->toISOString(),
        ];
    }
}
