<?php

declare(strict_types=1);

namespace App\Enums\Notification;

enum NotificationChannel: string
{
    case Database = 'database';
    case Email = 'email';
    case Push = 'push';
}
