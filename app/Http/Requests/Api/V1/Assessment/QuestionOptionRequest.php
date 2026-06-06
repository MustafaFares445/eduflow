<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Assessment;

use Illuminate\Foundation\Http\FormRequest;

final class QuestionOptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'questionId' => ['required', 'integer', 'exists:questions,id'],
            'optionText' => ['required', 'string'],
            'isCorrect' => ['sometimes', 'boolean'],
            'position' => ['required', 'integer', 'min:1'],
        ];
    }
}
