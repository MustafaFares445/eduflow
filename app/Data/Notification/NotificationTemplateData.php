<?php

declare(strict_types=1);

namespace App\Data\Notification;

use Spatie\LaravelData\Data;

final class NotificationTemplateData extends Data
{
    public function __construct(
        public string $key,
        public string $title,
        public string $body,
        public ?array $channels = null,
        public ?array $variables = null,
        public bool $isActive = true,
    ) {}
}
