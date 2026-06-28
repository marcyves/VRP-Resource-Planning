<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\Company;
use App\Models\Status;
use App\Models\User;
use App\Services\CompanyProvisioner;
use Database\Seeders\CompanySeeder;
use Database\Seeders\StatusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyManagementTest extends TestCase
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

    public function test_super_admin_can_update_company_terminology_profile(): void
    {
        $superAdmin = $this->makeSuperAdmin();
        $company = Company::factory()->create([
            'terminology_profile' => Company::PROFILE_EDUCATION,
        ]);

        $this->actingAs($superAdmin)
            ->patch(route('super-admin.companies.update', $company), [
                'terminology_profile' => Company::PROFILE_MEDICAL,
            ])
            ->assertRedirect(route('super-admin.companies.show', $company));

        $this->assertSame(Company::PROFILE_MEDICAL, $company->fresh()->terminology_profile);
    }

    public function test_super_admin_can_add_user_to_company(): void
    {
        $superAdmin = $this->makeSuperAdmin();
        $company = Company::factory()->create();

        $this->actingAs($superAdmin)
            ->post(route('super-admin.companies.users.store', $company), [
                'name' => 'Bob Reader',
                'email' => 'bob@example.test',
                'password' => 'Password1!',
                'password_confirmation' => 'Password1!',
                'status_id' => Status::READER,
            ])
            ->assertRedirect(route('super-admin.companies.show', $company));

        $this->assertDatabaseHas('users', [
            'email' => 'bob@example.test',
            'company_id' => $company->id,
            'status_id' => Status::READER,
            'mode' => 'Browse',
        ]);
    }

    public function test_super_admin_can_delete_company_and_users(): void
    {
        $superAdmin = $this->makeSuperAdmin();

        $provisioner = app(CompanyProvisioner::class);
        ['company' => $company, 'admin' => $admin] = $provisioner->provision([
            'company_name' => 'To Delete SA',
            'bill_prefix' => 'TDL',
            'terminology_profile' => Company::PROFILE_CONSULTING,
            'admin_name' => 'Admin Delete',
            'admin_email' => 'delete@example.test',
            'admin_password' => 'Password1!',
        ]);

        $companyId = $company->id;
        $adminId = $admin->id;

        $this->actingAs($superAdmin)
            ->delete(route('super-admin.companies.destroy', $company))
            ->assertRedirect(route('super-admin.companies.index'));

        $this->assertDatabaseMissing('companies', ['id' => $companyId]);
        $this->assertDatabaseMissing('users', ['id' => $adminId]);
    }

    public function test_super_admin_can_view_company_detail_page(): void
    {
        $superAdmin = $this->makeSuperAdmin();
        $company = Company::factory()->create(['name' => 'Visible Corp']);

        $this->actingAs($superAdmin)
            ->get(route('super-admin.companies.show', $company))
            ->assertOk()
            ->assertSee('Visible Corp');
    }

    private function makeSuperAdmin(): User
    {
        return User::factory()->create([
            'company_id' => null,
            'status_id' => Status::superAdminId(),
        ]);
    }
}
