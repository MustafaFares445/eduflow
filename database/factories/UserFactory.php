<?php

namespace Database\Factories;

use App\Enums\Auth\AccountStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'phone' => fake()->unique()->numerify('+9639########'),
            'telegram_username' => 'user_'.fake()->unique()->numerify('########'),
            'phone_verified_at' => now(),
            'account_status' => AccountStatus::Approved,
            'account_reviewed_at' => now(),
            'rejection_reason' => null,
            'is_admin' => false,
            'bio' => fake()->optional()->sentence(),
            'timezone' => fake()->timezone(),
            'locale' => fake()->randomElement(['en', 'ar']),
            'is_active' => true,
            'last_login_at' => now(),
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
            'phone_verified_at' => null,
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'account_status' => AccountStatus::Pending,
            'account_reviewed_at' => null,
            'account_reviewed_by' => null,
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_admin' => true,
            'account_status' => AccountStatus::Approved,
        ]);
    }
}
