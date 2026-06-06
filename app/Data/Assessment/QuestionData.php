<?php

declare(strict_types=1);

namespace App\Data\Assessment;

use Spatie\LaravelData\Data;

final class QuestionData extends Data
{
    public function __construct(
        public int $assessmentId,
        public string $type,
        public string $questionText,
        public ?string $explanation = null,
        public int $points = 1,
        public int $position,
        public bool $isActive = true,
    ) {}
}
