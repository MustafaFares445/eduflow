<?php

declare(strict_types=1);

namespace App\Enums\Learning;

enum LessonType: string
{
    case Video = 'video';
    case Text = 'text';
    case File = 'file';
    case Quiz = 'quiz';
    case Mixed = 'mixed';
}
