<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;

final class VerifyOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $phone = preg_replace('/\s+/', '', trim((string) $this->input('phone')));

        $this->merge([
            'phone' => $phone,
            'deviceName' => $this->input('deviceName', $this->input('device_name')),
        ]);
    }

    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'max:50'],
            'otp' => ['required', 'digits:6'],
            'deviceName' => ['nullable', 'string', 'max:255'],
        ];
    }
}
