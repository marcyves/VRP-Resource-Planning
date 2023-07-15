<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course>
 */
class CourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Course: '.rand(1,10),
            'school_id' => fake()->randomElement(School::all()),
            'sessions' => 8,
            'session_length' => 2.0,
            'year' => '2023',
            'semester' => 'S1',
            'rate' => 87.50
        ];
    }
}
