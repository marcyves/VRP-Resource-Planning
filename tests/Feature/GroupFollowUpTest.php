<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Course;
use App\Models\Group;
use App\Models\LoginEvent;
use App\Models\Planning;
use App\Models\Program;
use App\Models\School;
use App\Models\Status;
use App\Models\User;
use Database\Seeders\StatusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupFollowUpTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            StatusSeeder::class,
        ]);
    }

    public function test_editor_can_view_group_follow_up(): void
    {
        $company = Company::factory()->create();
        $teacher = $this->makeEditor($company);
        $reader = $this->makeReader($company, [
            'name' => 'Léa Martin',
            'email' => 'lea.martin@example.test',
        ]);

        LoginEvent::factory()->successful()->create([
            'company_id' => $company->id,
            'user_id' => $reader->id,
            'username' => $reader->email,
            'ip' => '203.0.113.77',
            'geo_label' => 'Lyon, France',
            'occurred_at' => now()->subHour(),
        ]);

        $this->actingAs($teacher)
            ->get(route('group-follow-up.index'))
            ->assertOk()
            ->assertSee(__('messages.group_follow_up'), false)
            ->assertSee('Léa Martin', false)
            ->assertSee('lea.martin@example.test', false)
            ->assertSee(__('messages.group_follow_up_connected'), false)
            ->assertDontSee('203.0.113.77', false)
            ->assertDontSee('Lyon, France', false);
    }

    public function test_editor_cannot_view_admin_login_journal(): void
    {
        $teacher = $this->makeEditor();

        $this->actingAs($teacher)
            ->get(route('login-stats.index'))
            ->assertForbidden();
    }

    public function test_reader_cannot_view_group_follow_up(): void
    {
        $reader = $this->makeReader();

        $this->actingAs($reader)
            ->get(route('group-follow-up.index'))
            ->assertForbidden();
    }

    public function test_company_admin_can_view_follow_up_and_journal(): void
    {
        $admin = $this->makeCompanyAdmin();

        $this->actingAs($admin)
            ->get(route('group-follow-up.index'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('login-stats.index'))
            ->assertOk();
    }

    public function test_super_admin_is_kept_off_group_follow_up(): void
    {
        $superAdmin = User::factory()->create([
            'company_id' => null,
            'status_id' => Status::superAdminId(),
        ]);

        $this->actingAs($superAdmin)
            ->get(route('group-follow-up.index'))
            ->assertRedirect(route('super-admin.companies.index'));
    }

    public function test_teacher_sees_only_readers_of_assigned_schools(): void
    {
        $company = Company::factory()->create();
        $otherCompany = Company::factory()->create();

        $schoolA = School::factory()->create([
            'name' => 'Site Alpha',
            'company_id' => $company->id,
        ]);
        $schoolB = School::factory()->create([
            'name' => 'Site Beta',
            'company_id' => $company->id,
        ]);

        $teacher = $this->makeEditor($company);
        $teacher->schools()->attach($schoolA->id);

        $inClass = $this->makeReader($company, [
            'name' => 'In Class',
            'email' => 'in-class@example.test',
        ]);
        $inClass->schools()->attach($schoolA->id);

        $otherClass = $this->makeReader($company, [
            'name' => 'Other Class',
            'email' => 'other-class@example.test',
        ]);
        $otherClass->schools()->attach($schoolB->id);

        $foreign = $this->makeReader($otherCompany, [
            'name' => 'Foreign Reader',
            'email' => 'foreign-reader@example.test',
        ]);

        $this->actingAs($teacher)
            ->get(route('group-follow-up.index'))
            ->assertOk()
            ->assertSee('in-class@example.test', false)
            ->assertDontSee('other-class@example.test', false)
            ->assertDontSee('foreign-reader@example.test', false);
    }

    public function test_unassigned_teacher_sees_all_company_readers(): void
    {
        $company = Company::factory()->create();
        $teacher = $this->makeEditor($company);
        $this->makeReader($company, [
            'name' => 'Company Reader',
            'email' => 'company-reader@example.test',
        ]);

        $this->actingAs($teacher)
            ->get(route('group-follow-up.index'))
            ->assertOk()
            ->assertSee('company-reader@example.test', false);
    }

    public function test_teacher_does_not_see_admin_or_editor_on_roster(): void
    {
        $company = Company::factory()->create();
        $teacher = $this->makeEditor($company, [
            'name' => 'Teacher Self',
            'email' => 'teacher-self@example.test',
        ]);
        $this->makeCompanyAdmin($company, [
            'name' => 'Company Admin',
            'email' => 'company-admin@example.test',
        ]);
        $this->makeReader($company, [
            'name' => 'Only Reader',
            'email' => 'only-reader@example.test',
        ]);

        $this->actingAs($teacher)
            ->get(route('group-follow-up.index'))
            ->assertOk()
            ->assertSee('only-reader@example.test', false)
            ->assertDontSee('teacher-self@example.test', false)
            ->assertDontSee('company-admin@example.test', false);
    }

    public function test_idle_reader_is_listed_as_not_connected(): void
    {
        $company = Company::factory()->create();
        $teacher = $this->makeEditor($company);
        $this->makeReader($company, [
            'name' => 'Idle Student',
            'email' => 'idle-student@example.test',
        ]);

        $this->actingAs($teacher)
            ->get(route('group-follow-up.index'))
            ->assertOk()
            ->assertSee('idle-student@example.test', false)
            ->assertSee(__('messages.group_follow_up_idle'), false)
            ->assertSee(__('messages.group_follow_up_never'), false);
    }

    public function test_teacher_sees_only_groups_of_assigned_schools_with_session_counts(): void
    {
        $company = Company::factory()->create();
        $schoolA = School::factory()->create([
            'name' => 'Site Alpha',
            'company_id' => $company->id,
        ]);
        $schoolB = School::factory()->create([
            'name' => 'Site Beta',
            'company_id' => $company->id,
        ]);

        $teacher = $this->makeEditor($company);
        $teacher->schools()->attach($schoolA->id);

        $program = Program::factory()->create(['company_id' => $company->id]);
        $courseA = Course::factory()->create([
            'name' => 'Cours Alpha',
            'short_name' => 'CA',
            'school_id' => $schoolA->id,
            'program_id' => $program->id,
        ]);
        $courseB = Course::factory()->create([
            'name' => 'Cours Beta',
            'short_name' => 'CB',
            'school_id' => $schoolB->id,
            'program_id' => $program->id,
        ]);

        $groupA = Group::create([
            'name' => 'Groupe Alpha',
            'short_name' => 'GA',
            'size' => 12,
            'company_id' => $company->id,
            'active' => true,
            'year' => now()->format('Y'),
        ]);
        $groupB = Group::create([
            'name' => 'Groupe Beta',
            'short_name' => 'GB',
            'size' => 8,
            'company_id' => $company->id,
            'active' => true,
            'year' => now()->format('Y'),
        ]);
        $groupA->courses()->attach($courseA->id);
        $groupB->courses()->attach($courseB->id);

        Planning::query()->create([
            'begin' => now()->subDay()->setTime(9, 0),
            'end' => now()->subDay()->setTime(11, 0),
            'location' => 'Salle 1',
            'group_id' => $groupA->id,
            'course_id' => $courseA->id,
        ]);

        $this->actingAs($teacher)
            ->get(route('group-follow-up.index'))
            ->assertOk()
            ->assertSee('Groupe Alpha', false)
            ->assertSee('Cours Alpha', false)
            ->assertDontSee('Groupe Beta', false);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function makeCompanyAdmin(?Company $company = null, array $overrides = []): User
    {
        $company ??= Company::factory()->create();

        return User::factory()->create(array_merge([
            'company_id' => $company->id,
            'status_id' => Status::ADMIN,
        ], $overrides));
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function makeEditor(?Company $company = null, array $overrides = []): User
    {
        $company ??= Company::factory()->create();

        return User::factory()->create(array_merge([
            'company_id' => $company->id,
            'status_id' => Status::EDITOR,
        ], $overrides));
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function makeReader(?Company $company = null, array $overrides = []): User
    {
        $company ??= Company::factory()->create();

        return User::factory()->create(array_merge([
            'company_id' => $company->id,
            'status_id' => Status::READER,
        ], $overrides));
    }
}
