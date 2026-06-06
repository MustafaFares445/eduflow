<?php

declare(strict_types=1);

namespace App\Enums\Assessment;

enum AssessmentStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';
}
