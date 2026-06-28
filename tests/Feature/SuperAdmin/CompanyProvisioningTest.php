<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\Company;
use App\Models\Status;
use App\Models\User;
use Database\Seeders\CompanySeeder;
use Database\Seeders\StatusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyProvisioningTest extends TestCase
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

    public function test_super_admin_can_create_company_and_administrator(): void
    {
        $superAdmin = User::factory()->create([
            'company_id' => null,
            'status_id' => Status::superAdminId(),
        ]);

        $response = $this->actingAs($superAdmin)->post(route('super-admin.companies.store'), [
            'company_name' => 'Acme Formation',
            'bill_prefix' => 'ACM',
            'terminology_profile' => Company::PROFILE_EDUCATION,
            'admin_name' => 'Alice Admin',
            'admin_email' => 'alice@acme.test',
            'admin_password' => 'Password1!',
            'admin_password_confirmation' => 'Password1!',
        ]);

        $response->assertRedirect(route('super-admin.companies.index'));
        $this->assertDatabaseHas('companies', [
            'name' => 'Acme Formation',
            'bill_prefix' => 'ACM',
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'alice@acme.test',
            'status_id' => Status::ADMIN,
        ]);

        $company = Company::where('bill_prefix', 'ACM')->first();
        $admin = User::where('email', 'alice@acme.test')->first();

        $this->assertSame($company->id, $admin->company_id);
        $this->assertSame($admin->id, $company->contact_user_id);
    }

    public function test_tenant_user_cannot_access_super_admin_routes(): void
    {
        $user = User::factory()->create([
            'status_id' => Status::ADMIN,
        ]);

        $this->actingAs($user)
            ->get(route('super-admin.companies.index'))
            ->assertForbidden();
    }

    public function test_super_admin_is_redirected_from_tenant_home(): void
    {
        $superAdmin = User::factory()->create([
            'company_id' => null,
            'status_id' => Status::SUPER_ADMIN,
        ]);

        $this->actingAs($superAdmin)
            ->get(route('home'))
            ->assertRedirect(route('super-admin.companies.index'));
    }
}
