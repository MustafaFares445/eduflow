<?php

declare(strict_types=1);

namespace App\Data\Learning;

use Spatie\LaravelData\Data;

final class CourseModuleData extends Data
{
    public function __construct(
        public int $courseId,
        public string $title,
        public ?string $description,
        public int $position,
        public bool $isActive = true,
    ) {}
}
