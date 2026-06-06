<?php

declare(strict_types=1);

namespace App\Enums\Learning;

enum CourseLevel: string
{
    case Beginner = 'beginner';
    case Intermediate = 'intermediate';
    case Advanced = 'advanced';
}
