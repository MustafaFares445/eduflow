<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Learning;

use Illuminate\Foundation\Http\FormRequest;

final class LessonReorderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer', 'exists:lessons,id'],
            'items.*.position' => ['required', 'integer', 'min:1'],
        ];
    }
}
