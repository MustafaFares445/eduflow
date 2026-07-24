<?php

declare(strict_types=1);

namespace App\Data\Auth;

use Spatie\LaravelData\Data;

final class VerifyOtpData extends Data
{
    public function __construct(
        public string $phone,
        public string $otp,
        public ?string $deviceName = null,
    ) {}
}
