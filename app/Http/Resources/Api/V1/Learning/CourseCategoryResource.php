<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1\Learning;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class CourseCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'parentId' => $this->parent_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'icon' => $this->icon,
            'color' => $this->color,
            'imageUrl' => method_exists($this->resource, 'getFirstMediaUrl')
                ? ($this->getFirstMediaUrl('image') ?: null)
                : null,
            'isActive' => (bool) $this->is_active,
            'sortOrder' => (int) $this->sort_order,
            'parent' => $this->relationLoaded('parent') ? self::make($this->parent) : null,
            'children' => $this->relationLoaded('children') ? self::collection($this->children) : null,
            'coursesCount' => (int) ($this->courses_count ?? 0),
            'createdAt' => $this->created_at?->toISOString(),
            'updatedAt' => $this->updated_at?->toISOString(),
        ];
    }
}
