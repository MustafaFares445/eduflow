<?php

declare(strict_types=1);

namespace App\Services\Notification;

use App\Data\Notification\NotificationPreferenceData;
use App\Models\User;
use App\Models\UserNotificationPreference;

final class NotificationPreferenceService
{
    public function show(User $user): UserNotificationPreference
    {
        return UserNotificationPreference::firstOrCreate(
            ['user_id' => $user->id],
            [
                'email_enabled' => true,
                'push_enabled' => true,
                'in_app_enabled' => true,
                'course_updates_enabled' => true,
                'assessment_updates_enabled' => true,
            ]
        )->fresh(['user']);
    }

    public function update(User $user, NotificationPreferenceData $data): UserNotificationPreference
    {
        $preference = UserNotificationPreference::updateOrCreate(
            ['user_id' => $user->id],
            [
                'email_enabled' => $data->emailEnabled,
                'push_enabled' => $data->pushEnabled,
                'in_app_enabled' => $data->inAppEnabled,
                'course_updates_enabled' => $data->courseUpdatesEnabled,
                'assessment_updates_enabled' => $data->assessmentUpdatesEnabled,
            ]
        );

        return $preference->fresh(['user']);
    }
}
