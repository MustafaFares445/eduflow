<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $updates = [];

        if ($this->has('fullName') || $this->has('full_name')) {
            $updates['name'] = $this->input('fullName', $this->input('full_name'));
        }

        if ($this->has('telegramUsername') || $this->has('telegram_username')) {
            $updates['telegramUsername'] = strtolower(ltrim(trim((string) $this->input(
                'telegramUsername',
                $this->input('telegram_username')
            )), '@'));
        }

        if ($updates !== []) {
            $this->merge($updates);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'telegramUsername' => [
                'sometimes',
                'string',
                'min:3',
                'max:64',
                'regex:/^[a-z0-9_]+$/',
                Rule::unique('users', 'telegram_username')->ignore($this->user()?->id),
            ],
            'bio' => ['nullable', 'string'],
            'timezone' => ['nullable', 'string', 'max:64'],
            'locale' => ['nullable', 'string', 'max:10'],
        ];
    }
}
