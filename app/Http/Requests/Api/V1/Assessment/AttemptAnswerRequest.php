<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Assessment;

use Illuminate\Foundation\Http\FormRequest;

final class AttemptAnswerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'questionId' => ['required', 'integer', 'exists:questions,id'],
            'selectedOptionId' => ['nullable', 'integer', 'exists:question_options,id'],
            'answerText' => ['nullable', 'string'],
            'answerJson' => ['nullable', 'array'],
        ];
    }
}
