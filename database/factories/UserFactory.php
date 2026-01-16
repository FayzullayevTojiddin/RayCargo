<?php

namespace Database\Factories;

use App\Enums\User\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        $loginType = fake()->randomElement(['email', 'phone']);

        return [
            'email' => $loginType === 'email'
                ? fake()->unique()->safeEmail()
                : null,

            'phone_number' => $loginType === 'phone'
                ? fake()->unique()->numerify('998#########')
                : null,

            'image' => null,

            'email_verified_at' => $loginType === 'email' ? now() : null,
            'phone_verified_at' => $loginType === 'phone' ? now() : null,

            'login_type' => $loginType,

            'last_login_at' => now(),

            'role' => fake()->randomElement(
                array_column(UserRole::cases(), 'value')
            ),
            'status' => fake()->randomElement(['active', 'inactive']),

            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }
}