<?php

declare(strict_types=1);

namespace App\Enums\Learning;

enum LessonProgressStatus: string
{
    case NotStarted = 'not_started';
    case InProgress = 'in_progress';
    case Completed = 'completed';
}
