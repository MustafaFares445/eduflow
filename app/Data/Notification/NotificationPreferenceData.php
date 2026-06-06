<?php

declare(strict_types=1);

namespace App\Data\Notification;

use Spatie\LaravelData\Data;

final class NotificationPreferenceData extends Data
{
    public function __construct(
        public bool $emailEnabled = true,
        public bool $pushEnabled = true,
        public bool $inAppEnabled = true,
        public bool $courseUpdatesEnabled = true,
        public bool $assessmentUpdatesEnabled = true,
    ) {}
}
