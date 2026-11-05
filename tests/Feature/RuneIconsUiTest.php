<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Database\Seeders\CompanySeeder;
use Database\Seeders\StatusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RuneIconsUiTest extends TestCase
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

    public function test_landing_uses_rune_duotone_feature_icons(): void
    {
        $this->get(route('welcome'))
            ->assertOk()
            ->assertSee('rune-icon', false)
            ->assertSee('var(--rune-duotone-primary, #00236f)', false)
            ->assertDontSee('stroke="currentColor"', false)
            ->assertDontSee('icons/add-circle-svgrepo-com.svg', false);
    }

    public function test_authenticated_shell_nav_and_planning_use_rune_icons(): void
    {
        $user = User::factory()->create([
            'company_id' => Company::query()->where('bill_prefix', 'XDM')->value('id'),
            'status_id' => 1,
            'mode' => 'Edit',
        ]);

        $html = $this->actingAs($user)
            ->get(route('planning.index'))
            ->assertOk()
            ->assertSee('rune-icon', false)
            ->assertSee('var(--rune-duotone-primary', false)
            ->assertDontSee('icons/trash.svg', false)
            ->assertDontSee('icons/edit.svg', false)
            ->getContent();

        $this->assertStringContainsString('sidebar-link__icon', $html);
        $this->assertGreaterThan(3, substr_count($html, 'class="rune-icon'));
    }

    public function test_treasury_invoice_empty_state_uses_rune_icon(): void
    {
        $user = User::factory()->create([
            'company_id' => Company::query()->where('bill_prefix', 'XDM')->value('id'),
            'status_id' => 1,
            'mode' => 'Edit',
        ]);

        $this->actingAs($user)
            ->get(route('treasury.invoices.index'))
            ->assertOk()
            ->assertSee('empty-state', false)
            ->assertSee('rune-icon', false)
            ->assertDontSee('icons/trash.svg', false)
            ->assertDontSee('icons/edit.svg', false);
    }
}
