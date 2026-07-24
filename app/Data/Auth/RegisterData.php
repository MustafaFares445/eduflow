<?php

declare(strict_types=1);

namespace App\Data\Auth;

use Spatie\LaravelData\Data;

final class RegisterData extends Data
{
    public function __construct(
        public string $fullName,
        public string $phone,
        public string $password,
        public ?string $locale = 'ar',
        public ?string $timezone = null,
    ) {}
}
