<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1\Notification;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class NotificationPreferenceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'userId' => $this->user_id,
            'emailEnabled' => (bool) $this->email_enabled,
            'pushEnabled' => (bool) $this->push_enabled,
            'inAppEnabled' => (bool) $this->in_app_enabled,
            'courseUpdatesEnabled' => (bool) $this->course_updates_enabled,
            'assessmentUpdatesEnabled' => (bool) $this->assessment_updates_enabled,
            'createdAt' => $this->created_at?->toISOString(),
            'updatedAt' => $this->updated_at?->toISOString(),
        ];
    }
}
