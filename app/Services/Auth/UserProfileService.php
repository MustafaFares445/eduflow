<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Data\Auth\UpdateProfileData;
use App\Models\User;
use Illuminate\Http\UploadedFile;

final class UserProfileService
{
    public function show(User $user): User
    {
        return $user->loadMissing(['notificationPreference', 'media']);
    }

    public function update(User $user, UpdateProfileData $data): User
    {
        $user->fill(array_filter([
            'name' => $data->name,
            'phone' => $data->phone,
            'telegram_username' => $data->telegramUsername,
            'bio' => $data->bio,
            'timezone' => $data->timezone,
            'locale' => $data->locale,
        ], static fn ($value): bool => $value !== null));

        $user->save();

        return $user->fresh(['notificationPreference', 'media']);
    }

    public function avatar(User $user, UploadedFile $avatar): User
    {
        $user->addMedia($avatar)->toMediaCollection('avatar');

        return $user->fresh(['notificationPreference', 'media']);
    }
}
