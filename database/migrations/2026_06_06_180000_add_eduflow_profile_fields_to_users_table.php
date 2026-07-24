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
            $table->string('telegram_username', 64)->nullable()->unique()->after('phone');
            $table->timestamp('phone_verified_at')->nullable()->after('telegram_username');
            $table->string('verification_code')->nullable()->after('phone_verified_at');
            $table->timestamp('verification_code_expires_at')->nullable()->after('verification_code');
            $table->string('account_status')->default('pending')->after('verification_code_expires_at')->index();
            $table->text('rejection_reason')->nullable()->after('account_status');
            $table->timestamp('account_reviewed_at')->nullable()->after('rejection_reason');
            $table->foreignId('account_reviewed_by')->nullable()->after('account_reviewed_at')->constrained('users')->nullOnDelete();
            $table->boolean('is_admin')->default(false)->after('account_reviewed_by');
            $table->text('bio')->nullable()->after('is_admin');
            $table->string('timezone')->nullable()->after('bio');
            $table->string('locale')->default('ar')->after('timezone');
            $table->boolean('is_active')->default(true)->after('locale');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('account_reviewed_by');
            $table->dropColumn([
                'phone',
                'telegram_username',
                'phone_verified_at',
                'verification_code',
                'verification_code_expires_at',
                'account_status',
                'rejection_reason',
                'account_reviewed_at',
                'is_admin',
                'bio',
                'timezone',
                'locale',
                'is_active',
                'last_login_at',
            ]);
        });
    }
};
