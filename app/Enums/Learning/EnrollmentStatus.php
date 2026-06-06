<?php

declare(strict_types=1);

namespace App\Enums\Learning;

enum EnrollmentStatus: string
{
    case Active = 'active';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}
