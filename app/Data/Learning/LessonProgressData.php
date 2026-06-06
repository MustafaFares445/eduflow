<?php

declare(strict_types=1);

namespace App\Data\Learning;

use Spatie\LaravelData\Data;

final class LessonProgressData extends Data
{
    public function __construct(
        public string $status,
        public int $progressSeconds = 0,
    ) {}
}
