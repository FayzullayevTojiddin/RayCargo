<?php

namespace Database\Factories;

use App\Models\ClientProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientProfileFactory extends Factory
{
    protected $model = ClientProfile::class;

    public function definition(): array
    {
        return [
            'user_id' => null,
            'is_online' => $this->faker->boolean(30),
            'is_active' => $this->faker->boolean(85),
            'last_seen_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
        ];
    }

    public function online(): static
    {
        return $this->state(fn () => [
            'is_online' => true,
            'last_seen_at' => now(),
        ]);
    }

    public function offline(): static
    {
        return $this->state(fn () => [
            'is_online' => false,
            'last_seen_at' => $this->faker->dateTimeBetween('-30 days', '-1 hour'),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => [
            'is_active' => false,
        ]);
    }
}