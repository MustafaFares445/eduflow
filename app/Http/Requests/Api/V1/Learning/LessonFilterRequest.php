<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Learning;

use App\Enums\Learning\LessonType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class LessonFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'perPage' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'search' => ['sometimes', 'string', 'max:255'],
            'filter.courseId' => ['sometimes', 'integer', 'exists:courses,id'],
            'filter.courseModuleId' => ['sometimes', 'integer', 'exists:course_modules,id'],
            'filter.type' => ['sometimes', Rule::enum(LessonType::class)],
            'filter.isPreview' => ['sometimes', 'boolean'],
            'filter.isActive' => ['sometimes', 'boolean'],
            'sort' => ['sometimes', 'string'],
        ];
    }
}
