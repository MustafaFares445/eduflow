<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Learning;

use Illuminate\Foundation\Http\FormRequest;

final class LessonMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'video' => ['nullable', 'file', 'mimes:mp4,mov,webm', 'max:1024000'],
            'files' => ['nullable', 'array'],
            'files.*' => ['file', 'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip', 'max:51200'],
            'images' => ['nullable', 'array'],
            'images.*' => ['file', 'mimes:jpeg,jpg,png,webp', 'max:4096'],
        ];
    }
}
