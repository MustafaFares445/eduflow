<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Learning;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Learning\RedeemEnrollmentCodeRequest;
use App\Http\Resources\Api\V1\Learning\EnrollmentResource;
use App\Models\Enrollment;
use App\Models\EnrollmentCode;
use App\Services\Learning\EnrollmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class EnrollmentCodeController extends Controller
{
    public function redeem(
        RedeemEnrollmentCodeRequest $request,
        EnrollmentService $enrollmentService,
    ): JsonResponse {
        $user = $request->user();
        $codeValue = $request->validated('code');

        $enrollment = DB::transaction(function () use ($user, $codeValue, $enrollmentService): Enrollment {
            $code = EnrollmentCode::query()
                ->where('code_hash', EnrollmentCode::hash($codeValue))
                ->lockForUpdate()
                ->first();

            if (! $code || ! $code->isRedeemable()) {
                throw ValidationException::withMessages([
                    'code' => __('The enrollment code is invalid, expired, or already used.'),
                ]);
            }

            $existingRedemption = DB::table('enrollment_code_redemptions')
                ->where('enrollment_code_id', $code->id)
                ->where('user_id', $user->id)
                ->first();

            if ($existingRedemption) {
                return Enrollment::query()
                    ->with(['user', 'course.category', 'course.media'])
                    ->findOrFail($existingRedemption->enrollment_id);
            }

            $enrollment = $enrollmentService->activate($user, $code->course, $code->access_days);

            DB::table('enrollment_code_redemptions')->insert([
                'enrollment_code_id' => $code->id,
                'user_id' => $user->id,
                'enrollment_id' => $enrollment->id,
                'redeemed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $code->increment('redemptions_count');

            return $enrollment;
        });

        return response()->json([
            'data' => EnrollmentResource::make($enrollment)->resolve(),
            'message' => __('Course activated successfully.'),
        ]);
    }
}
