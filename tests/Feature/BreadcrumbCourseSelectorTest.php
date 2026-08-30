<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Program;
use App\Models\School;
use App\Models\User;
use Database\Seeders\CompanySeeder;
use Database\Seeders\StatusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BreadcrumbCourseSelectorTest extends TestCase
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

    public function test_planning_breadcrumb_lists_courses_regardless_of_agenda_year(): void
    {
        $user = User::factory()->create(['company_id' => 2]);
        $school = School::factory()->create(['company_id' => 2, 'name' => 'Test School']);
        $program = Program::factory()->create(['company_id' => 2]);

        $course = Course::factory()->create([
            'school_id' => $school->id,
            'program_id' => $program->id,
            'year' => 2026,
            'name' => 'Cross Year Course',
        ]);

        $this->actingAs($user)
            ->withSession([
                'school_id' => $school->id,
                'school' => $school->name,
                'current_year' => 2027,
                'current_month' => 1,
            ])
            ->get(route('planning.index'))
            ->assertOk()
            ->assertSee('id="breadcrumb-course"', false)
            ->assertSee('Cross Year Course', false)
            ->assertSee('value="'.$course->id.'"', false);
    }
}
