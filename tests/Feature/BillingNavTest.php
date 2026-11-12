<?php

namespace Tests\Feature;

use App\Models\Company;
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

    private function companyUser(): User
    {
        return User::factory()->create([
            'company_id' => Company::query()->where('bill_prefix', 'XDM')->value('id'),
        ]);
    }

    public function test_legacy_billing_nav_opens_the_prepare_list(): void
    {
        $user = $this->companyUser();

        $this->actingAs($user)
            ->get(route('nav.billing'))
            ->assertRedirect(route('billing.prepare'));
    }

    public function test_prepare_list_renders_without_a_school_in_session(): void
    {
        $user = $this->companyUser();

        $this->actingAs($user)
            ->get(route('billing.prepare'))
            ->assertOk()
            ->assertSee(__('messages.billing_prepare_empty'), false);
    }

    public function test_daily_sidebar_links_schools_and_groups(): void
    {
        $user = $this->companyUser();

        $this->actingAs($user)
            ->get(route('planning.index'))
            ->assertOk()
            ->assertSee(route('billing.prepare'), false)
            ->assertSee(route('treasury.invoices.index'), false)
            ->assertSee(route('treasury.expenses.index'), false)
            ->assertSee(route('treasury.bank.index'), false)
            ->assertSee(route('home'), false)
            ->assertSee(route('group.index'), false)
            ->assertSee(route('program.index'), false)
            ->assertSee(__('messages.nav_daily'), false)
            ->assertSee(__('messages.nav_activity'), false)
            ->assertSee(__('messages.nav_finance'), false)
            ->assertSee(__('messages.programs'), false)
            ->assertSee(__('messages.invoices'), false)
            ->assertSee(__('messages.expenses'), false)
            ->assertSee(__('messages.bank'), false);
    }

    public function test_sidebar_groups_activity_then_finance_before_more(): void
    {
        $user = $this->companyUser();

        $html = $this->actingAs($user)
            ->get(route('planning.index'))
            ->assertOk()
            ->getContent();

        $activity = strpos($html, __('messages.nav_activity'));
        $programs = strpos($html, 'href="'.e(route('program.index')).'"');
        $finance = strpos($html, __('messages.nav_finance'));
        $invoices = strpos($html, 'href="'.e(route('treasury.invoices.index')).'"');
        $expenses = strpos($html, 'href="'.e(route('treasury.expenses.index')).'"');
        $bank = strpos($html, 'href="'.e(route('treasury.bank.index')).'"');
        $more = strpos($html, __('messages.nav_more'));

        $this->assertNotFalse($activity);
        $this->assertNotFalse($programs);
        $this->assertNotFalse($finance);
        $this->assertNotFalse($invoices);
        $this->assertNotFalse($expenses);
        $this->assertNotFalse($bank);
        $this->assertNotFalse($more);
        $this->assertTrue($activity < $programs && $programs < $finance, 'Programme belongs in Activité, before Finance');
        $this->assertTrue($finance < $invoices && $invoices < $expenses && $expenses < $bank, 'Finance lists Factures, Dépenses, then Banque');
        $this->assertTrue($bank < $more, 'Banque is in Finance, not under Plus');
    }
}
