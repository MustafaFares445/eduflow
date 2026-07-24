<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Auth\ProfileController;
use App\Http\Controllers\Api\V1\Catalog\CatalogController;
use App\Http\Controllers\Api\V1\Learning\EnrollmentCodeController;
use App\Http\Controllers\Api\V1\Learning\EnrollmentController;
use App\Http\Controllers\Api\V1\Learning\LessonProgressController;
use App\Http\Controllers\Api\V1\Learning\StudentLearningController;
use App\Http\Controllers\Api\V1\Notification\NotificationController;
use App\Http\Controllers\Api\V1\Notification\NotificationPreferenceController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('auth/resend-otp', [AuthController::class, 'resendOtp'])->middleware('throttle:3,1');
    Route::post('auth/login', [AuthController::class, 'login']);

    Route::get('catalog/categories', [CatalogController::class, 'categories']);
    Route::get('catalog/courses', [CatalogController::class, 'courses']);
    Route::get('catalog/courses/{course}', [CatalogController::class, 'course']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::post('auth/logout-all', [AuthController::class, 'logoutAll']);

        Route::get('me', [ProfileController::class, 'show']);
        Route::get('me/account-status', [ProfileController::class, 'show']);
        Route::patch('me', [ProfileController::class, 'update']);
        Route::post('me/avatar', [ProfileController::class, 'avatar']);

        Route::middleware('account.approved')->group(function (): void {
            Route::get('me/learning-summary', [StudentLearningController::class, 'summary']);

            Route::get('me/enrollments', [EnrollmentController::class, 'mine']);
            Route::get('me/enrollments/{enrollment}', [EnrollmentController::class, 'showMine']);
            Route::get('me/courses/{course}', [StudentLearningController::class, 'course']);
            Route::post('enrollment-codes/redeem', [EnrollmentCodeController::class, 'redeem'])->middleware('throttle:10,1');

            Route::patch('lessons/{lesson}/progress', [LessonProgressController::class, 'update']);

            Route::get('notifications', [NotificationController::class, 'index']);
            Route::patch('notifications/{notification}/read', [NotificationController::class, 'read']);
            Route::patch('notifications/read-all', [NotificationController::class, 'readAll']);
            Route::get('me/notification-preferences', [NotificationPreferenceController::class, 'show']);
            Route::patch('me/notification-preferences', [NotificationPreferenceController::class, 'update']);
        });
    });
});
