<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Assessment;

use App\Enums\Assessment\AssessmentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class AssessmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'courseId' => ['nullable', 'integer', 'exists:courses,id', 'required_without:lessonId'],
            'lessonId' => ['nullable', 'integer', 'exists:lessons,id', 'required_without:courseId'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', Rule::enum(AssessmentType::class)],
            'status' => ['sometimes', 'string', 'max:50'],
            'passingScore' => ['sometimes', 'integer', 'min:0', 'max:100'],
            'maxAttempts' => ['nullable', 'integer', 'min:1'],
            'timeLimitMinutes' => ['nullable', 'integer', 'min:1'],
            'shuffleQuestions' => ['sometimes', 'boolean'],
            'showResultImmediately' => ['sometimes', 'boolean'],
            'position' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
