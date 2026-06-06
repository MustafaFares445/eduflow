<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Learning;

use App\Enums\Learning\LessonProgressStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class LessonProgressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(LessonProgressStatus::class)],
            'progressSeconds' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
