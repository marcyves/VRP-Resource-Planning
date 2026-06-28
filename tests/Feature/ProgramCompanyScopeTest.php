<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Program;
use App\Models\Status;
use App\Models\User;
use Database\Seeders\CompanySeeder;
use Database\Seeders\StatusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgramCompanyScopeTest extends TestCase
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

    public function test_user_only_sees_programs_for_their_company(): void
    {
        $companyA = Company::factory()->create(['name' => 'Alpha', 'bill_prefix' => 'ALP']);
        $companyB = Company::factory()->create(['name' => 'Beta', 'bill_prefix' => 'BET']);

        $programA = Program::factory()->create([
            'name' => 'Programme Alpha',
            'company_id' => $companyA->id,
        ]);
        Program::factory()->create([
            'name' => 'Programme Beta',
            'company_id' => $companyB->id,
        ]);

        $user = User::factory()->create([
            'company_id' => $companyA->id,
            'status_id' => Status::ADMIN,
        ]);

        $response = $this->actingAs($user)->get(route('program.index'));

        $response->assertOk();
        $response->assertSee('Programme Alpha');
        $response->assertDontSee('Programme Beta');
        $response->assertSee((string) $programA->id);
    }

    public function test_user_cannot_open_another_company_program(): void
    {
        $companyA = Company::factory()->create(['name' => 'Alpha', 'bill_prefix' => 'ALP']);
        $companyB = Company::factory()->create(['name' => 'Beta', 'bill_prefix' => 'BET']);

        $foreignProgram = Program::factory()->create([
            'name' => 'Programme Beta',
            'company_id' => $companyB->id,
        ]);

        $user = User::factory()->create([
            'company_id' => $companyA->id,
            'status_id' => Status::ADMIN,
        ]);

        $this->actingAs($user)
            ->get(route('program.show', $foreignProgram->id))
            ->assertNotFound();
    }

    public function test_created_program_is_attached_to_user_company(): void
    {
        $company = Company::factory()->create(['name' => 'Alpha', 'bill_prefix' => 'ALP']);

        $user = User::factory()->create([
            'company_id' => $company->id,
            'status_id' => Status::ADMIN,
        ]);

        $response = $this->actingAs($user)->post(route('program.store'), [
            'name' => 'Nouveau programme',
            'short_description' => 'NP',
        ]);

        $response->assertRedirect(route('program.index'));
        $this->assertDatabaseHas('programs', [
            'name' => 'Nouveau programme',
            'company_id' => $company->id,
        ]);
    }
}
