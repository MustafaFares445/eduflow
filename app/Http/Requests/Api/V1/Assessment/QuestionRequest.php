<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Assessment;

use App\Enums\Assessment\QuestionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class QuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'assessmentId' => ['required', 'integer', 'exists:assessments,id'],
            'type' => ['required', Rule::enum(QuestionType::class)],
            'questionText' => ['required', 'string'],
            'explanation' => ['nullable', 'string'],
            'points' => ['sometimes', 'integer', 'min:1'],
            'position' => ['required', 'integer', 'min:1'],
            'isActive' => ['sometimes', 'boolean'],
        ];
    }
}
