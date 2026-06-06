<?php

declare(strict_types=1);

namespace App\Data\Learning;

use Spatie\LaravelData\Data;

final class CourseCategoryData extends Data
{
    public function __construct(
        public ?int $parentId,
        public string $name,
        public ?string $description = null,
        public ?string $icon = null,
        public ?string $color = null,
        public bool $isActive = true,
        public int $sortOrder = 0,
    ) {}
}
