<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Learning;

use App\Enums\Learning\CourseLevel;
use App\Enums\Learning\CourseStatus;
use App\Enums\Learning\CourseVisibility;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class CourseFilterRequest extends FormRequest
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
            'filter.categoryId' => ['sometimes', 'integer', 'exists:course_categories,id'],
            'filter.instructorId' => ['sometimes', 'integer', 'exists:users,id'],
            'filter.level' => ['sometimes', Rule::enum(CourseLevel::class)],
            'filter.status' => ['sometimes', Rule::enum(CourseStatus::class)],
            'filter.visibility' => ['sometimes', Rule::enum(CourseVisibility::class)],
            'filter.publishedAfter' => ['sometimes', 'date'],
            'filter.publishedBefore' => ['sometimes', 'date'],
            'sort' => ['sometimes', 'string'],
        ];
    }
}
