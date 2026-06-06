<?php

declare(strict_types=1);

namespace App\Data\Assessment;

use Spatie\LaravelData\Data;

final class AssessmentAttemptData extends Data
{
    public function __construct(
        public int $assessmentId,
        public int $userId,
        public string $status = 'started',
        public ?string $startedAt = null,
        public ?string $expiresAt = null,
        public int $attemptNumber = 1,
    ) {}
}
