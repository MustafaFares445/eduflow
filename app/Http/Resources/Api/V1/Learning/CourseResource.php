<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1\Learning;

use App\Http\Resources\Api\V1\Assessment\AssessmentResource;
use App\Http\Resources\Api\V1\MediaResource;
use App\Http\Resources\Api\V1\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class CourseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'categoryId' => $this->category_id,
            'instructorId' => $this->instructor_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'shortDescription' => $this->short_description,
            'description' => $this->description,
            'level' => $this->level?->value ?? $this->level,
            'language' => $this->language,
            'status' => $this->status?->value ?? $this->status,
            'visibility' => $this->visibility?->value ?? $this->visibility,
            'durationMinutes' => (int) $this->duration_minutes,
            'lessonsCount' => (int) $this->lessons_count,
            'studentsCount' => (int) $this->students_count,
            'averageRating' => $this->average_rating,
            'ratingsCount' => (int) $this->ratings_count,
            'publishedAt' => $this->published_at?->toISOString(),
            'category' => $this->relationLoaded('category') ? CourseCategoryResource::make($this->category) : null,
            'instructor' => $this->relationLoaded('instructor') ? \App\Http\Resources\Api\V1\UserResource::make($this->instructor) : null,
            'modules' => $this->relationLoaded('modules') ? CourseModuleResource::collection($this->modules) : null,
            'lessons' => $this->relationLoaded('lessons') ? LessonResource::collection($this->lessons) : null,
            'assessments' => $this->relationLoaded('assessments') ? AssessmentResource::collection($this->assessments) : null,
            'reviewsCount' => (int) ($this->reviews_count ?? $this->ratings_count ?? 0),
            'cover' => $this->relationLoaded('media') ? MediaResource::collection($this->media->where('collection_name', 'cover')->values()) : null,
            'introVideo' => $this->relationLoaded('media') ? MediaResource::collection($this->media->where('collection_name', 'intro_video')->values()) : null,
            'attachments' => $this->relationLoaded('media') ? MediaResource::collection($this->media->where('collection_name', 'attachments')->values()) : null,
            'createdAt' => $this->created_at?->toISOString(),
            'updatedAt' => $this->updated_at?->toISOString(),
        ];
    }
}
