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
        $this->merge([
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
