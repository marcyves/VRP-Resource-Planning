<?php

namespace Database\Factories;

use App\Models\LoginEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LoginEvent>
 */
class LoginEventFactory extends Factory
{
    protected $model = LoginEvent::class;

    public function definition(): array
    {
        return [
            'company_id' => null,
            'user_id' => null,
            'username' => fake()->unique()->safeEmail(),
            'ip' => fake()->ipv4(),
            'geo_label' => fake()->city().', France',
            'success' => fake()->boolean(),
            'locked_out' => false,
            'occurred_at' => now(),
        ];
    }

    public function successful(): static
    {
        return $this->state(fn (array $attributes) => [
            'success' => true,
            'locked_out' => false,
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'success' => false,
            'locked_out' => false,
        ]);
    }
}
