<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;

final class RejectUserAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'rejectionReason' => $this->input('rejectionReason', $this->input('rejection_reason')),
        ]);
    }

    public function rules(): array
    {
        return [
            'rejectionReason' => ['required', 'string', 'max:2000'],
        ];
    }
}
