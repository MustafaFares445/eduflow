<?php

declare(strict_types=1);

namespace App\Enums\Assessment;

enum AttemptStatus: string
{
    case Started = 'started';
    case Submitted = 'submitted';
    case Expired = 'expired';
    case Cancelled = 'cancelled';
}
