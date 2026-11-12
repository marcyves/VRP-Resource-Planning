<?php

namespace Tests\Feature;

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

    public function test_legacy_billing_nav_opens_the_prepare_list(): void
    {
        $user = User::factory()->create(['company_id' => 2]);

        $this->actingAs($user)
            ->get(route('nav.billing'))
            ->assertRedirect(route('billing.prepare'));
    }

    public function test_prepare_list_renders_without_a_school_in_session(): void
    {
        $user = User::factory()->create(['company_id' => 2]);

        $this->actingAs($user)
            ->get(route('billing.prepare'))
            ->assertOk()
            ->assertSee(__('messages.billing_prepare_empty'), false);
    }

    public function test_daily_sidebar_links_schools_and_groups(): void
    {
        $user = User::factory()->create(['company_id' => 2]);

        $this->actingAs($user)
            ->get(route('planning.index'))
            ->assertOk()
            ->assertSee(route('billing.prepare'), false)
            ->assertSee(route('treasury.invoices.index'), false)
            ->assertSee(route('treasury.expenses.index'), false)
            ->assertSee(route('home'), false)
            ->assertSee(route('group.index'), false)
            ->assertSee(__('messages.nav_daily'), false)
            ->assertSee(__('messages.nav_activity'), false);
    }
}
