<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Learning;

use App\Enums\Learning\CourseLevel;
use App\Enums\Learning\CourseStatus;
use App\Enums\Learning\CourseVisibility;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class CourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'categoryId' => ['required', 'integer', 'exists:course_categories,id'],
            'instructorId' => ['nullable', 'integer', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'shortDescription' => ['nullable', 'string', 'max:1000'],
            'description' => ['nullable', 'string'],
            'level' => ['required', Rule::enum(CourseLevel::class)],
            'language' => ['sometimes', 'string', 'max:10'],
            'status' => ['sometimes', Rule::enum(CourseStatus::class)],
            'visibility' => ['sometimes', Rule::enum(CourseVisibility::class)],
            'publishedAt' => ['nullable', 'date'],
        ];
    }
}
