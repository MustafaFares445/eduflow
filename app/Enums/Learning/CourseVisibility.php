<?php

declare(strict_types=1);

namespace App\Enums\Learning;

enum CourseVisibility: string
{
    case Public = 'public';
    case Private = 'private';
    case Unlisted = 'unlisted';
}
