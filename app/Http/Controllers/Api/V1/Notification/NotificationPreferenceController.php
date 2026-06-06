<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Notification;

use App\Data\Notification\NotificationPreferenceData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Notification\NotificationPreferenceRequest;
use App\Http\Resources\Api\V1\Notification\NotificationPreferenceResource;
use App\Services\Notification\NotificationPreferenceService;
use Illuminate\Http\Request;

final class NotificationPreferenceController extends Controller
{
    public function __construct(
        private readonly NotificationPreferenceService $notificationPreferenceService,
    ) {}

    public function show(Request $request): NotificationPreferenceResource
    {
        return NotificationPreferenceResource::make(
            $this->notificationPreferenceService->show($request->user())
        );
    }

    public function update(NotificationPreferenceRequest $request): NotificationPreferenceResource
    {
        return NotificationPreferenceResource::make(
            $this->notificationPreferenceService->update(
                $request->user(),
                NotificationPreferenceData::from($request->validated())
            )
        );
    }
}
