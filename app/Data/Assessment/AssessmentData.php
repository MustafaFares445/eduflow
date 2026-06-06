<?php

declare(strict_types=1);

namespace App\Data\Assessment;

use Spatie\LaravelData\Data;

final class AssessmentData extends Data
{
    public function __construct(
        public ?int $courseId,
        public ?int $lessonId,
        public string $title,
        public ?string $description = null,
        public string $type,
        public string $status = 'draft',
        public int $passingScore = 60,
        public ?int $maxAttempts = null,
        public ?int $timeLimitMinutes = null,
        public bool $shuffleQuestions = false,
        public bool $showResultImmediately = true,
        public int $position = 0,
    ) {}
}
