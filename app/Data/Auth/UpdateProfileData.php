<?php

declare(strict_types=1);

namespace App\Data\Auth;

use Spatie\LaravelData\Data;

final class UpdateProfileData extends Data
{
    public function __construct(
        public ?string $name = null,
        public ?string $telegramUsername = null,
        public ?string $bio = null,
        public ?string $timezone = null,
        public ?string $locale = null,
    ) {}
}
