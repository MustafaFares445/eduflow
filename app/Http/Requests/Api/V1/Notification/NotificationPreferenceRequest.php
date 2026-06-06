<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Notification;

use Illuminate\Foundation\Http\FormRequest;

final class NotificationPreferenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'emailEnabled' => ['sometimes', 'boolean'],
            'pushEnabled' => ['sometimes', 'boolean'],
            'inAppEnabled' => ['sometimes', 'boolean'],
            'courseUpdatesEnabled' => ['sometimes', 'boolean'],
            'assessmentUpdatesEnabled' => ['sometimes', 'boolean'],
        ];
    }
}
