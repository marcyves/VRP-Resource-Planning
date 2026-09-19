<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\User;
use App\Support\SchoolContext;
use Database\Seeders\CompanySeeder;
use Database\Seeders\StatusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchoolContextTest extends TestCase
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

    public function test_school_defaults_to_education_context(): void
    {
        $school = School::factory()->create(['company_id' => 2]);

        $this->assertSame(SchoolContext::EDUCATION, $school->fresh()->context);
        $this->assertFalse($school->fresh()->isMentoring());
    }

    public function test_school_update_persists_mentoring_context(): void
    {
        $user = User::factory()->create(['company_id' => 2]);
        $school = School::factory()->create([
            'company_id' => 2,
            'context' => SchoolContext::EDUCATION,
        ]);

        $this->actingAs($user)
            ->put(route('school.update', $school->id), [
                'name' => $school->name,
                'code' => $school->code,
                'context' => SchoolContext::MENTORING,
            ])
            ->assertRedirect();

        $this->assertSame(SchoolContext::MENTORING, $school->fresh()->context);
        $this->assertTrue($school->fresh()->isMentoring());
    }

    public function test_mentoring_school_show_uses_overlay_labels(): void
    {
        $user = User::factory()->create(['company_id' => 2]);
        $school = School::factory()->create([
            'company_id' => 2,
            'context' => SchoolContext::MENTORING,
        ]);

        $this->actingAs($user)
            ->get(route('school.show', $school))
            ->assertOk()
            ->assertSee(__('messages.school_context_mentoring'), false)
            ->assertSee('Parc.', false)
            ->assertSee('Étud.', false);
    }

    public function test_education_school_show_keeps_standard_table_headers(): void
    {
        config(['app.locale' => 'fr']);

        $user = User::factory()->create(['company_id' => 2]);
        $school = School::factory()->create([
            'company_id' => 2,
            'context' => SchoolContext::EDUCATION,
        ]);

        $this->actingAs($user)
            ->get(route('school.show', $school))
            ->assertOk()
            ->assertSee(__('messages.course_table_th_program'), false)
            ->assertDontSee('Parc.', false);
    }

    public function test_home_list_unaffected_by_mentoring_school(): void
    {
        config(['app.locale' => 'fr']);

        $user = User::factory()->create(['company_id' => 2]);
        School::factory()->create([
            'company_id' => 2,
            'context' => SchoolContext::MENTORING,
            'name' => 'Mentor School',
        ]);

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertDontSee('Parc.', false)
            ->assertDontSee(__('messages.school_context_mentoring'), false);
    }
}
