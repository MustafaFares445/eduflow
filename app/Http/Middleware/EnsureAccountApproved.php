<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\Auth\AccountStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureAccountApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $status = $user?->account_status?->value ?? $user?->account_status;

        if ($user !== null && $user->isApproved()) {
            return $next($request);
        }

        $message = match ($status) {
            AccountStatus::Rejected->value => __('Your account was rejected by the administrator.'),
            default => __('Your account is pending administrator approval.'),
        };

        return response()->json([
            'message' => $message,
            'accountStatus' => $status,
            'rejectionReason' => $user?->rejection_reason,
        ], 403);
    }
}
