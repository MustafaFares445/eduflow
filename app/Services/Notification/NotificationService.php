<?php

declare(strict_types=1);

namespace App\Services\Notification;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Notifications\DatabaseNotification;

final class NotificationService
{
    public function index(User $user, int $perPage = 20): LengthAwarePaginator
    {
        return $user->notifications()
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function read(User $user, DatabaseNotification $notification): DatabaseNotification
    {
        abort_unless($notification->notifiable_type === User::class && (int) $notification->notifiable_id === (int) $user->id, 404);

        $notification->markAsRead();

        return $notification->refresh();
    }

    public function readAll(User $user): void
    {
        $user->unreadNotifications()->update(['read_at' => now()]);
    }
}
