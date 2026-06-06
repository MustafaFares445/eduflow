<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Learning;

use Illuminate\Foundation\Http\FormRequest;

final class CourseMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cover' => ['nullable', 'file', 'mimes:jpeg,jpg,png,webp', 'max:4096'],
            'introVideo' => ['nullable', 'file', 'mimes:mp4,mov,webm', 'max:512000'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip', 'max:51200'],
        ];
    }
}
