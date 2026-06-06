<?php

declare(strict_types=1);

namespace App\Data\Assessment;

use Spatie\LaravelData\Data;

final class QuestionOptionData extends Data
{
    public function __construct(
        public int $questionId,
        public string $optionText,
        public bool $isCorrect = false,
        public int $position,
    ) {}
}
