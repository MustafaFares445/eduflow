<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Course;
use App\Models\EnrollmentCode;
use App\Models\User;
use Illuminate\Database\Seeder;

final class MobileDemoSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->where('email', 'student@eduflow.local')->update([
            'phone' => '+963999999999',
            'phone_verified_at' => now(),
        ]);

        User::query()->where('email', 'instructor@eduflow.local')->update([
            'phone' => '+963988888888',
            'phone_verified_at' => now(),
        ]);

        $course = Course::query()->where('slug', 'foundations-of-maths')->first();

        if ($course) {
            EnrollmentCode::query()->updateOrCreate(
                ['code_hash' => EnrollmentCode::hash('EDUFLOW-DEMO-2026')],
                [
                    'course_id' => $course->id,
                    'label' => 'Demo mobile activation code',
                    'access_days' => 365,
                    'max_redemptions' => 100,
                    'redemptions_count' => 0,
                    'is_active' => true,
                ]
            );
        }
    }
}
