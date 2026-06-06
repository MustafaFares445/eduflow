<?php

declare(strict_types=1);

namespace App\Data\Learning;

use Spatie\LaravelData\Data;

final class LessonData extends Data
{
    public function __construct(
        public int $courseId,
        public int $courseModuleId,
        public string $title,
        public string $type,
        public ?string $body,
        public int $durationSeconds = 0,
        public int $position,
        public bool $isPreview = false,
        public bool $isActive = true,
        public ?string $publishedAt = null,
    ) {}
}
