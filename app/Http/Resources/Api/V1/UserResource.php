<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => str_ends_with((string) $this->email, '@phone.eduflow.local') ? null : $this->email,
            'phone' => $this->phone,
            'phoneVerifiedAt' => $this->phone_verified_at?->toISOString(),
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
