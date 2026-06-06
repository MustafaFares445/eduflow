<?php

declare(strict_types=1);

namespace App\Enums\Assessment;

enum QuestionType: string
{
    case MultipleChoice = 'multiple_choice';
    case TrueFalse = 'true_false';
    case FreeText = 'free_text';
}
