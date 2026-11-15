<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Course;
use App\Models\Invoice;
use App\Models\Program;
use App\Models\School;
use App\Models\Status;
use App\Models\User;
use Database\Seeders\StatusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SchoolHomeBudgetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(StatusSeeder::class);

        if (DB::connection()->getDriverName() === 'mysql') {
            $mode = (string) DB::selectOne('select @@SESSION.sql_mode as mode')->mode;
            if (! str_contains($mode, 'ONLY_FULL_GROUP_BY')) {
                $prefix = $mode === '' ? '' : $mode.',';
                DB::statement("set session sql_mode = '{$prefix}ONLY_FULL_GROUP_BY'");
            }
        }
    }

    public function test_new_company_admin_can_open_home(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->get(route('home'))
            ->assertOk();
    }

    public function test_login_as_company_admin_redirects_to_home(): void
    {
        $admin = $this->makeAdmin();

        $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($admin);

        $this->get(route('home'))->assertOk();
    }

    public function test_home_lists_school_and_year_invoice_total(): void
    {
        $year = (int) now()->format('Y');
        $context = $this->makeSchoolWithInvoice($year, 240);

        $this->actingAs($context['admin'])
            ->withSession(['current_year' => (string) $year])
            ->get(route('home'))
            ->assertOk()
            ->assertSee($context['school']->name, false)
            ->assertSee('240.00€', false);
    }

    public function test_get_schools_and_budget_sums_invoices_without_grouping_schools(): void
    {
        $year = (int) now()->format('Y');
        $context = $this->makeSchoolWithInvoice($year, 120);
        Invoice::create([
            'id' => 'BUD002',
            'description' => 'Deuxième',
            'bill_date' => "{$year}-06-01",
            'amount' => 80,
            'company_id' => $context['company']->id,
            'school_id' => $context['school']->id,
        ]);

        $sqls = [];
        DB::listen(function ($query) use (&$sqls) {
            $sqls[] = $query->sql;
        });

        $schools = $context['admin']->getSchoolsAndBudget($year);

        $this->assertCount(1, $schools);
        $this->assertEqualsWithDelta(200, (float) $schools->first()->amount, 0.02);

        $budgetSql = collect($sqls)->first(function (string $sql) {
            $normalized = strtolower($sql);

            return (str_contains($normalized, 'from `schools`')
                || str_contains($normalized, 'from "schools"'))
                && str_contains($normalized, 'amount');
        });

        $this->assertNotNull($budgetSql);
        $this->assertDoesNotMatchRegularExpression(
            '/group\s+by\s+[`"]?schools[`"]?\./i',
            $budgetSql
        );
        $this->assertMatchesRegularExpression(
            '/select\s+coalesce\(sum\(/i',
            $budgetSql
        );
    }

    private function makeAdmin(?Company $company = null): User
    {
        $company ??= Company::factory()->create();

        return User::factory()->create([
            'company_id' => $company->id,
            'status_id' => Status::ADMIN,
            'mode' => 'Edit',
            'password' => 'password',
        ]);
    }

    /**
     * @return array{admin: User, company: Company, school: School}
     */
    private function makeSchoolWithInvoice(int $year, float $amount): array
    {
        $company = Company::factory()->create();
        $admin = $this->makeAdmin($company);

        $school = School::query()->create([
            'name' => 'Client Alpha',
            'code' => 'ALP',
            'company_id' => $company->id,
        ]);

        $program = Program::factory()->create(['company_id' => $company->id]);

        Course::factory()->create([
            'school_id' => $school->id,
            'program_id' => $program->id,
            'name' => 'Cours Alpha',
            'year' => $year,
        ]);

        Invoice::create([
            'id' => 'BUD001',
            'description' => 'Facture test',
            'bill_date' => "{$year}-03-15",
            'amount' => $amount,
            'company_id' => $company->id,
            'school_id' => $school->id,
        ]);

        return [
            'admin' => $admin,
            'company' => $company,
            'school' => $school,
        ];
    }
}
