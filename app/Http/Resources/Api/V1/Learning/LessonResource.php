<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1\Learning;

use App\Http\Resources\Api\V1\MediaResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class LessonResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $video = $this->relationLoaded('media')
            ? $this->media->firstWhere('collection_name', 'video')
            : null;

        return [
            'id' => $this->id,
            'courseId' => $this->course_id,
            'courseModuleId' => $this->course_module_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'type' => $this->type?->value ?? $this->type,
            'body' => $this->body,
            'durationSeconds' => (int) $this->duration_seconds,
            'position' => (int) $this->position,
            'isPreview' => (bool) $this->is_preview,
            'isActive' => (bool) $this->is_active,
            'publishedAt' => $this->published_at?->toISOString(),
            'course' => $this->relationLoaded('course') ? CourseResource::make($this->course) : null,
            'courseModule' => $this->relationLoaded('courseModule') ? CourseModuleResource::make($this->courseModule) : null,
            'assessment' => $this->relationLoaded('assessment') ? AssessmentSummaryResource::make($this->assessment) : null,
            'videoUrl' => $video?->getUrl(),
            'video' => $this->relationLoaded('media') ? MediaResource::collection($this->media->where('collection_name', 'video')->values()) : null,
            'files' => $this->relationLoaded('media') ? MediaResource::collection($this->media->where('collection_name', 'files')->values()) : null,
            'images' => $this->relationLoaded('media') ? MediaResource::collection($this->media->where('collection_name', 'images')->values()) : null,
            'createdAt' => $this->created_at?->toISOString(),
            'updatedAt' => $this->updated_at?->toISOString(),
        ];
    }
}
