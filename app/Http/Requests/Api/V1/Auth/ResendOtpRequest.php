<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;

final class ResendOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone' => preg_replace('/\s+/', '', trim((string) $this->input('phone'))),
        ]);
    }

    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'max:50', 'exists:users,phone'],
        ];
    }
}
