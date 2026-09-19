<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\User;
use Database\Seeders\CompanySeeder;
use Database\Seeders\StatusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingNavTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            StatusSeeder::class,
            CompanySeeder::class,
        ]);
    }

    public function test_billing_nav_redirects_to_home_without_school_context(): void
    {
        $user = User::factory()->create(['company_id' => 2]);

        $this->actingAs($user)
            ->get(route('nav.billing'))
            ->assertRedirect(route('home'))
            ->assertSessionHas('billing_needs_school', true);
    }

    public function test_billing_nav_redirects_to_school_billing_when_school_in_session(): void
    {
        $user = User::factory()->create(['company_id' => 2]);
        $school = School::factory()->create(['company_id' => 2]);

        $this->actingAs($user)
            ->withSession(['school_id' => $school->id])
            ->get(route('nav.billing'))
            ->assertRedirect(route('school.show', $school).'?focus=billing#billing');
    }

    public function test_billing_nav_uses_last_school_id_when_school_id_cleared(): void
    {
        $user = User::factory()->create(['company_id' => 2]);
        $school = School::factory()->create(['company_id' => 2]);

        $this->actingAs($user)
            ->withSession(['last_school_id' => $school->id])
            ->get(route('nav.billing'))
            ->assertRedirect(route('school.show', $school).'?focus=billing#billing');
    }
}
