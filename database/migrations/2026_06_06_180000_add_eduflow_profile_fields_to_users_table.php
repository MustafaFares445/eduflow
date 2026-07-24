<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('phone', 50)->nullable()->unique()->after('password');
            $table->timestamp('phone_verified_at')->nullable()->after('phone');
            $table->string('verification_code')->nullable()->after('phone_verified_at');
            $table->timestamp('verification_code_expires_at')->nullable()->after('verification_code');
            $table->text('bio')->nullable()->after('verification_code_expires_at');
            $table->string('timezone')->nullable()->after('bio');
            $table->string('locale')->default('ar')->after('timezone');
            $table->boolean('is_active')->default(true)->after('locale');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'phone',
                'phone_verified_at',
                'verification_code',
                'verification_code_expires_at',
                'bio',
                'timezone',
                'locale',
                'is_active',
                'last_login_at',
            ]);
        });
    }
};
