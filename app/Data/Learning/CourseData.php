<?php

declare(strict_types=1);

namespace App\Data\Learning;

use Spatie\LaravelData\Data;

final class CourseData extends Data
{
    public function __construct(
        public int $categoryId,
        public ?int $instructorId,
        public string $title,
        public ?string $shortDescription = null,
        public ?string $description = null,
        public string $level,
        public ?string $language = 'en',
        public ?string $status = 'draft',
        public ?string $visibility = 'public',
        public ?string $publishedAt = null,
    ) {}
}
