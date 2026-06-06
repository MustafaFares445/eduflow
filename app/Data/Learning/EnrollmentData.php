<?php

declare(strict_types=1);

namespace App\Data\Learning;

use Spatie\LaravelData\Data;

final class EnrollmentData extends Data
{
    public function __construct(
        public int $userId,
        public int $courseId,
        public string $status = 'active',
    ) {}
}
