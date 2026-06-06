<?php

declare(strict_types=1);

namespace App\Enums\Assessment;

enum AssessmentType: string
{
    case Quiz = 'quiz';
    case Exam = 'exam';
    case Practice = 'practice';
}
