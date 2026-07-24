<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enrollments', function (Blueprint $table): void {
            $table->timestamp('expires_at')->nullable()->after('last_accessed_at')->index();
        });

        Schema::create('enrollment_codes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->string('code_hash', 64)->unique();
            $table->string('label')->nullable();
            $table->unsignedInteger('access_days')->nullable();
            $table->unsignedInteger('max_redemptions')->default(1);
            $table->unsignedInteger('redemptions_count')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['course_id', 'is_active']);
            $table->index('expires_at');
        });

        Schema::create('enrollment_code_redemptions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('enrollment_code_id')->constrained('enrollment_codes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('enrollment_id')->constrained('enrollments')->cascadeOnDelete();
            $table->timestamp('redeemed_at');
            $table->timestamps();
            $table->unique(['enrollment_code_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollment_code_redemptions');
        Schema::dropIfExists('enrollment_codes');

        Schema::table('enrollments', function (Blueprint $table): void {
            $table->dropColumn('expires_at');
        });
    }
};
