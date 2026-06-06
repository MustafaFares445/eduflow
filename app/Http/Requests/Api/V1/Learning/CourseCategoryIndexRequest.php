<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Learning;

use Illuminate\Foundation\Http\FormRequest;

final class CourseCategoryIndexRequest extends FormRequest
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
            'filter.parentId' => ['sometimes', 'integer', 'exists:course_categories,id'],
            'filter.isActive' => ['sometimes', 'boolean'],
            'sort' => ['sometimes', 'string'],
        ];
    }
}
