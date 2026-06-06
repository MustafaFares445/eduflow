<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Notification;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class NotificationTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $uniqueKey = Rule::unique('notification_templates', 'key');
        if ($notificationTemplate = $this->route('notification_template')) {
            $uniqueKey = $uniqueKey->ignore($notificationTemplate);
        }

        return [
            'key' => ['required', 'string', 'max:255', $uniqueKey],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'channels' => ['nullable', 'array'],
            'variables' => ['nullable', 'array'],
            'isActive' => ['sometimes', 'boolean'],
        ];
    }
}
