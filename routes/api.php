<?php

use App\Http\Controllers\Api\V1\Assessment\AssessmentAttemptController;
use App\Http\Controllers\Api\V1\Assessment\AssessmentController;
use App\Http\Controllers\Api\V1\Assessment\QuestionController;
use App\Http\Controllers\Api\V1\Assessment\QuestionOptionController;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Auth\ProfileController;
use App\Http\Controllers\Api\V1\Catalog\CatalogController;
use App\Http\Controllers\Api\V1\Learning\CourseCategoryController;
use App\Http\Controllers\Api\V1\Learning\CourseController;
use App\Http\Controllers\Api\V1\Learning\CourseModuleController;
use App\Http\Controllers\Api\V1\Learning\EnrollmentController;
use App\Http\Controllers\Api\V1\Learning\LessonController;
use App\Http\Controllers\Api\V1\Learning\LessonProgressController;
use App\Http\Controllers\Api\V1\Notification\NotificationController;
use App\Http\Controllers\Api\V1\Notification\NotificationPreferenceController;
use App\Http\Controllers\Api\V1\Notification\NotificationTemplateController;
use App\Http\Controllers\Api\V1\Report\ReportController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('catalog/categories', [CatalogController::class, 'categories']);
    Route::get('catalog/courses', [CatalogController::class, 'courses']);
    Route::get('catalog/courses/{course}', [CatalogController::class, 'course']);
    Route::get('catalog/courses/{course}/modules', [CatalogController::class, 'modules']);
    Route::get('catalog/lessons/{lesson}', [CatalogController::class, 'lesson']);

    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::post('auth/logout-all', [AuthController::class, 'logoutAll']);
        Route::get('me', [ProfileController::class, 'show']);
        Route::patch('me', [ProfileController::class, 'update']);
        Route::post('me/avatar', [ProfileController::class, 'avatar']);

        Route::apiResource('course-categories', CourseCategoryController::class);
        Route::apiResource('courses', CourseController::class);
        Route::post('courses/{course}/publish', [CourseController::class, 'publish']);
        Route::post('courses/{course}/archive', [CourseController::class, 'archive']);
        Route::post('courses/{course}/media', [CourseController::class, 'media']);
        Route::delete('courses/{course}/media/{media}', [CourseController::class, 'mediaDestroy']);

        Route::apiResource('course-modules', CourseModuleController::class);
        Route::post('course-modules/reorder', [CourseModuleController::class, 'reorder']);

        Route::apiResource('lessons', LessonController::class);
        Route::post('lessons/reorder', [LessonController::class, 'reorder']);
        Route::post('lessons/{lesson}/media', [LessonController::class, 'media']);
        Route::delete('lessons/{lesson}/media/{media}', [LessonController::class, 'mediaDestroy']);

        Route::post('courses/{course}/enroll', [EnrollmentController::class, 'store']);
        Route::get('me/enrollments', [EnrollmentController::class, 'mine']);
        Route::get('me/enrollments/{enrollment}', [EnrollmentController::class, 'showMine']);
        Route::patch('lessons/{lesson}/progress', [LessonProgressController::class, 'update']);
        Route::post('lessons/{lesson}/complete', [LessonProgressController::class, 'complete']);

        Route::apiResource('assessments', AssessmentController::class);
        Route::post('assessments/{assessment}/publish', [AssessmentController::class, 'publish']);
        Route::post('assessments/{assessment}/archive', [AssessmentController::class, 'archive']);

        Route::apiResource('questions', QuestionController::class);
        Route::post('questions/reorder', [QuestionController::class, 'reorder']);

        Route::apiResource('question-options', QuestionOptionController::class);

        Route::get('assessments/{assessment}/attempts', [AssessmentAttemptController::class, 'index']);
        Route::post('assessments/{assessment}/attempts', [AssessmentAttemptController::class, 'start']);
        Route::get('attempts/{assessmentAttempt}', [AssessmentAttemptController::class, 'show']);
        Route::post('attempts/{assessmentAttempt}/answers', [AssessmentAttemptController::class, 'saveAnswer']);
        Route::post('attempts/{assessmentAttempt}/submit', [AssessmentAttemptController::class, 'submit']);
        Route::get('attempts/{assessmentAttempt}/result', [AssessmentAttemptController::class, 'result']);
        Route::post('assessment-attempts/{assessmentAttempt}/grade', [AssessmentAttemptController::class, 'grade']);

        Route::get('notifications', [NotificationController::class, 'index']);
        Route::patch('notifications/{notification}/read', [NotificationController::class, 'read']);
        Route::patch('notifications/read-all', [NotificationController::class, 'readAll']);

        Route::apiResource('notification-templates', NotificationTemplateController::class);
        Route::get('me/notification-preferences', [NotificationPreferenceController::class, 'show']);
        Route::patch('me/notification-preferences', [NotificationPreferenceController::class, 'update']);

        Route::get('reports/learning/overview', [ReportController::class, 'learningOverview']);
        Route::get('reports/courses/{course}', [ReportController::class, 'course']);
        Route::get('reports/users/{user}/progress', [ReportController::class, 'userProgress']);
        Route::get('reports/assessments/{assessment}', [ReportController::class, 'assessment']);
        Route::post('reports/learning/export', [ReportController::class, 'exportLearning']);
    });
});
