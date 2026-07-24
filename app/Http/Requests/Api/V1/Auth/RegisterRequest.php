<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;

final class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $phone = preg_replace('/\s+/', '', trim((string) $this->input('phone')));
        $telegramUsername = strtolower(ltrim(trim((string) $this->input(
            'telegramUsername',
            $this->input('telegram_username')
        )), '@'));

        $this->merge([
            'fullName' => $this->input('fullName', $this->input('full_name')),
            'phone' => $phone,
            'telegramUsername' => $telegramUsername,
        ]);
    }

    public function rules(): array
    {
        return [
            'fullName' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50', 'unique:users,phone'],
            'telegramUsername' => ['required', 'string', 'min:3', 'max:64', 'regex:/^[a-z0-9_]+$/', 'unique:users,telegram_username'],
            'password' => ['required', 'string', 'min:8'],
            'locale' => ['nullable', 'string', 'max:10'],
            'timezone' => ['nullable', 'string', 'max:64'],
        ];
    }
}
