<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response|JsonResponse
    {
        if ((bool) $request->user()?->is_admin) {
            return $next($request);
        }

        return response()->json([
            'message' => __('Administrator access is required.'),
        ], 403);
    }
}
