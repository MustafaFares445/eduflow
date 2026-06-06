<?php

declare(strict_types=1);

namespace App\Data\Assessment;

use Spatie\LaravelData\Data;

final class AttemptAnswerData extends Data
{
    public function __construct(
        public int $questionId,
        public ?int $selectedOptionId = null,
        public ?string $answerText = null,
        public ?array $answerJson = null,
    ) {}
}
