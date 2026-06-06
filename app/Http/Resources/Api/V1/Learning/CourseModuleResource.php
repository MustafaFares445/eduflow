<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1\Learning;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class CourseModuleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'courseId' => $this->course_id,
            'title' => $this->title,
            'description' => $this->description,
            'position' => (int) $this->position,
            'isActive' => (bool) $this->is_active,
            'course' => $this->relationLoaded('course') ? CourseResource::make($this->course) : null,
            'lessons' => $this->relationLoaded('lessons') ? LessonResource::collection($this->lessons) : null,
            'createdAt' => $this->created_at?->toISOString(),
            'updatedAt' => $this->updated_at?->toISOString(),
        ];
    }
}
