<?php

declare(strict_types=1);

namespace App\Data\Learning;

use Spatie\LaravelData\Data;

final class CourseReviewData extends Data
{
    public function __construct(
        public int $userId,
        public int $courseId,
        public int $rating,
        public ?string $comment = null,
        public bool $isVisible = true,
    ) {}
}
