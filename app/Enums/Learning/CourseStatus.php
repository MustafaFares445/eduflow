<?php

declare(strict_types=1);

namespace App\Enums\Learning;

enum CourseStatus: string
{
    case Draft = 'draft';
    case Review = 'review';
    case Published = 'published';
    case Archived = 'archived';
}
