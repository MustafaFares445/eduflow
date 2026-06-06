<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Notification;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Notification\NotificationResource;
use App\Services\Notification\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

final class NotificationController extends Controller
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {}

    public function index(Request $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        return NotificationResource::collection(
            $this->notificationService->index($request->user(), (int) $request->integer('perPage', 20))
        );
    }

    public function read(Request $request, DatabaseNotification $notification): NotificationResource
    {
        return NotificationResource::make(
            $this->notificationService->read($request->user(), $notification)
        );
    }

    public function readAll(Request $request): JsonResponse
    {
        $this->notificationService->readAll($request->user());

        return response()->json([
            'message' => __('Notifications marked as read.'),
        ]);
    }
}
