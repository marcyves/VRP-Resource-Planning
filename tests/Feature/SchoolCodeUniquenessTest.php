<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\School;
use App\Models\Status;
use App\Models\User;
use Database\Seeders\CompanySeeder;
use Database\Seeders\StatusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchoolCodeUniquenessTest extends TestCase
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

    public function test_store_persists_school_code(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)
            ->post(route('school.store'), [
                'name' => 'École Alpha',
                'code' => ' ALPHA ',
            ])
            ->assertRedirect(route('school.index'));

        $this->assertDatabaseHas('schools', [
            'name' => 'École Alpha',
            'code' => 'ALPHA',
            'company_id' => $user->company_id,
        ]);
    }

    public function test_store_rejects_duplicate_code_in_same_company(): void
    {
        $user = $this->makeUser();
        $this->createSchool($user->company_id, 'CLI01');

        $this->actingAs($user)
            ->from(route('school.index'))
            ->post(route('school.store'), [
                'name' => 'Autre école',
                'code' => 'CLI01',
            ])
            ->assertRedirect(route('school.index'))
            ->assertSessionHasErrors('code');

        $this->assertSame(
            1,
            School::query()->where('company_id', $user->company_id)->where('code', 'CLI01')->count()
        );
        $this->assertDatabaseMissing('schools', ['name' => 'Autre école']);
    }

    public function test_store_allows_same_code_in_another_company(): void
    {
        $user = $this->makeUser('XDM');
        $otherCompanyId = $this->companyId('DEMO');

        School::query()->create([
            'name' => 'École Demo',
            'company_id' => $otherCompanyId,
            'code' => 'SHARED',
        ]);

        $this->actingAs($user)
            ->post(route('school.store'), [
                'name' => 'École Beta',
                'code' => 'SHARED',
            ])
            ->assertRedirect(route('school.index'));

        $this->assertDatabaseHas('schools', [
            'name' => 'École Beta',
            'code' => 'SHARED',
            'company_id' => $user->company_id,
        ]);
    }

    public function test_store_allows_empty_code(): void
    {
        $user = $this->makeUser();
        $this->createSchool($user->company_id, null);

        $this->actingAs($user)
            ->post(route('school.store'), [
                'name' => 'Sans code',
                'code' => '',
            ])
            ->assertRedirect(route('school.index'));

        $this->assertDatabaseHas('schools', [
            'name' => 'Sans code',
            'code' => null,
        ]);
    }

    public function test_update_rejects_code_already_used_by_another_school(): void
    {
        $user = $this->makeUser();
        $this->createSchool($user->company_id, 'TAKEN');
        $school = $this->createSchool($user->company_id, 'FREE');

        $this->actingAs($user)
            ->from(route('school.edit', $school->id))
            ->put(route('school.update', $school->id), [
                'name' => $school->name,
                'code' => 'TAKEN',
            ])
            ->assertRedirect(route('school.edit', $school->id))
            ->assertSessionHasErrors('code');

        $this->assertSame('FREE', $school->fresh()->code);
    }

    public function test_update_keeps_the_same_code_on_the_same_school(): void
    {
        $user = $this->makeUser();
        $school = $this->createSchool($user->company_id, 'KEEP');

        $this->actingAs($user)
            ->put(route('school.update', $school->id), [
                'name' => 'Renommée',
                'code' => 'KEEP',
            ])
            ->assertRedirect();

        $this->assertSame('KEEP', $school->fresh()->code);
        $this->assertSame('Renommée', $school->fresh()->name);
    }

    private function makeUser(string $billPrefix = 'XDM'): User
    {
        return User::factory()->create([
            'company_id' => $this->companyId($billPrefix),
            'mode' => 'Edit',
            'password' => 'password',
            'status_id' => Status::query()->where('name', 'admin')->value('id') ?: Status::ADMIN,
        ]);
    }

    private function createSchool(int $companyId, ?string $code): School
    {
        return School::query()->create([
            'name' => 'École '.$code,
            'company_id' => $companyId,
            'code' => $code,
        ]);
    }

    private function companyId(string $billPrefix): int
    {
        return (int) Company::query()->where('bill_prefix', $billPrefix)->value('id');
    }
}
