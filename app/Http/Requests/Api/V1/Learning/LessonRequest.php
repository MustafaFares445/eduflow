<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Learning;

use App\Enums\Learning\LessonType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class LessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'courseId' => ['required', 'integer', 'exists:courses,id'],
            'courseModuleId' => ['required', 'integer', 'exists:course_modules,id'],
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(LessonType::class)],
            'body' => ['nullable', 'string'],
            'durationSeconds' => ['sometimes', 'integer', 'min:0'],
            'position' => ['required', 'integer', 'min:1'],
            'isPreview' => ['sometimes', 'boolean'],
            'isActive' => ['sometimes', 'boolean'],
            'publishedAt' => ['nullable', 'date'],
        ];
    }
}
