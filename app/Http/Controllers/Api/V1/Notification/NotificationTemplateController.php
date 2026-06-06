<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Notification;

use App\Data\Notification\NotificationTemplateData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Notification\NotificationTemplateRequest;
use App\Http\Resources\Api\V1\Notification\NotificationTemplateResource;
use App\Models\NotificationTemplate;
use App\Services\Notification\NotificationTemplateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class NotificationTemplateController extends Controller
{
    public function __construct(
        private readonly NotificationTemplateService $notificationTemplateService,
    ) {}

    public function index(Request $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        return NotificationTemplateResource::collection(
            $this->notificationTemplateService->index(
                (int) $request->integer('perPage', 20),
                $request->string('search')->toString() ?: null
            )
        );
    }

    public function store(NotificationTemplateRequest $request): NotificationTemplateResource
    {
        return NotificationTemplateResource::make(
            $this->notificationTemplateService->store(NotificationTemplateData::from($request->validated()))
        );
    }

    public function show(NotificationTemplate $notification_template): NotificationTemplateResource
    {
        return NotificationTemplateResource::make($this->notificationTemplateService->show($notification_template));
    }

    public function update(NotificationTemplateRequest $request, NotificationTemplate $notification_template): NotificationTemplateResource
    {
        return NotificationTemplateResource::make(
            $this->notificationTemplateService->update(
                $notification_template,
                NotificationTemplateData::from($request->validated())
            )
        );
    }

    public function destroy(NotificationTemplate $notification_template): JsonResponse
    {
        $this->notificationTemplateService->delete($notification_template);

        return response()->json(null, 204);
    }
}
