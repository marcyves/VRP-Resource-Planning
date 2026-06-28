<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Program>
 */
class ProgramFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Program: '.rand(1, 10),
            'short_description' => fake()->optional(0.6)->lexify('P-????'),
            'company_id' => \App\Models\Company::factory(),
        ];
    }
}
