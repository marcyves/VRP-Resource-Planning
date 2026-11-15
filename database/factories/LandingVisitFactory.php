<?php

namespace Database\Factories;

use App\Models\LandingVisit;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LandingVisit>
 */
class LandingVisitFactory extends Factory
{
    protected $model = LandingVisit::class;

    public function definition(): array
    {
        return [
            'visited_at' => now(),
            'visitor_id' => fake()->uuid(),
            'is_returning' => false,
            'page' => 'landing',
            'locale' => 'fr',
            'accept_language' => 'fr-FR,fr;q=0.9',
            'os' => 'Windows',
            'device_type' => 'desktop',
            'browser' => 'Chrome',
            'referer_url' => 'https://www.google.com/search?q=vrp+plan',
            'referer_host' => 'www.google.com',
            'keywords' => 'vrp plan',
            'utm_source' => null,
            'utm_medium' => null,
            'utm_campaign' => null,
            'utm_content' => null,
            'utm_term' => null,
            'landing_path' => '/',
            'landing_query' => null,
            'country' => 'France',
            'ip_hash' => hash('sha256', fake()->ipv4().'|test'),
            'cta' => null,
        ];
    }

    public function returning(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_returning' => true,
            'visitor_id' => $attributes['visitor_id'] ?? (string) Str::uuid(),
        ]);
    }

    public function mobile(): static
    {
        return $this->state(fn () => [
            'device_type' => 'mobile',
            'os' => 'iOS',
            'browser' => 'Safari',
        ]);
    }
}
