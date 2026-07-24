<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $accountStatus = $this->account_status?->value ?? $this->account_status;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => str_ends_with((string) $this->email, '@phone.eduflow.local') ? null : $this->email,
            'phone' => $this->phone,
            'telegramUsername' => $this->telegram_username,
            'phoneVerifiedAt' => $this->phone_verified_at?->toISOString(),
            'accountStatus' => $accountStatus,
            'isPending' => $accountStatus === 'pending',
            'isApproved' => $accountStatus === 'approved',
            'isRejected' => $accountStatus === 'rejected',
            'rejectionReason' => $this->rejection_reason,
            'accountReviewedAt' => $this->account_reviewed_at?->toISOString(),
            'bio' => $this->bio,
            'timezone' => $this->timezone,
            'locale' => $this->locale,
            'isActive' => (bool) $this->is_active,
            'lastLoginAt' => $this->last_login_at?->toISOString(),
            'avatarUrl' => $this->getFirstMediaUrl('avatar') ?: null,
            'createdAt' => $this->created_at?->toISOString(),
            'updatedAt' => $this->updated_at?->toISOString(),
        ];
    }
}
