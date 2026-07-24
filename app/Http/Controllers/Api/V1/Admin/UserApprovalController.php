<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\Auth\AccountStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\RejectUserAccountRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

final class UserApprovalController extends Controller
{
    public function pending(Request $request): AnonymousResourceCollection
    {
        $search = trim((string) $request->query('search', ''));

        $users = User::query()
            ->where('account_status', AccountStatus::Pending->value)
            ->where('is_admin', false)
            ->when($search !== '', fn ($query) => $query->where(function ($nested) use ($search): void {
                $nested
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('telegram_username', 'like', "%{$search}%");
            }))
            ->latest()
            ->paginate($request->integer('perPage', 20))
            ->withQueryString();

        return UserResource::collection($users);
    }

    public function approve(Request $request, User $user): JsonResponse
    {
        $this->ensureReviewable($request->user(), $user);

        $user->forceFill([
            'account_status' => AccountStatus::Approved,
            'rejection_reason' => null,
            'account_reviewed_at' => now(),
            'account_reviewed_by' => $request->user()->id,
        ])->save();

        return response()->json([
            'data' => UserResource::make($user->fresh(['media'])),
            'message' => __('User account approved successfully.'),
        ]);
    }

    public function reject(RejectUserAccountRequest $request, User $user): JsonResponse
    {
        $this->ensureReviewable($request->user(), $user);

        $user->forceFill([
            'account_status' => AccountStatus::Rejected,
            'rejection_reason' => $request->validated('rejectionReason'),
            'account_reviewed_at' => now(),
            'account_reviewed_by' => $request->user()->id,
        ])->save();

        return response()->json([
            'data' => UserResource::make($user->fresh(['media'])),
            'message' => __('User account rejected successfully.'),
        ]);
    }

    private function ensureReviewable(User $admin, User $user): void
    {
        $status = $user->account_status?->value ?? $user->account_status;

        if ($user->id === $admin->id || $user->is_admin || $status !== AccountStatus::Pending->value) {
            throw ValidationException::withMessages([
                'user' => __('Only pending non-admin user accounts can be reviewed.'),
            ]);
        }
    }
}
