<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Group;
use App\Models\Planning;
use App\Models\Program;
use App\Models\School;
use App\Models\User;
use Database\Seeders\CompanySeeder;
use Database\Seeders\StatusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanningViewTest extends TestCase
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

    public function test_planning_index_defaults_to_month_view(): void
    {
        $user = User::factory()->create(['company_id' => 2]);

        $this->actingAs($user)
            ->get(route('planning.index'))
            ->assertOk()
            ->assertSee(__('messages.month'), false)
            ->assertSee(__('messages.week'), false)
            ->assertSee('planning-view-toggle', false)
            ->assertDontSee('week-agenda', false);
    }

    public function test_planning_index_week_view_renders_seven_day_row(): void
    {
        $user = User::factory()->create(['company_id' => 2]);

        $this->actingAs($user)
            ->withSession([
                'current_year' => 2026,
                'current_month' => 8,
                'planning_week_start' => '2026-08-17',
            ])
            ->get(route('planning.index', ['view' => 'week']))
            ->assertOk()
            ->assertSee('week-agenda', false)
            ->assertSee('view=week', false)
            ->assertSee('2026-08-17', false)
            ->assertSee('2026-08-23', false)
            ->assertSee('8h', false)
            ->assertSee('20h', false);
    }

    public function test_planning_previous_in_week_view_goes_back_one_week(): void
    {
        $user = User::factory()->create(['company_id' => 2]);

        $this->actingAs($user)
            ->withSession([
                'planning_view' => 'week',
                'planning_week_start' => '2026-08-17',
                'current_month' => 8,
                'current_year' => 2026,
            ])
            ->get(route('planning.previous'))
            ->assertOk();

        $this->assertSame('week', session('planning_view'));
        $this->assertSame('2026-08-10', session('planning_week_start'));
    }

    public function test_week_view_matches_sessions_by_full_date_not_day_number(): void
    {
        $user = User::factory()->create(['company_id' => 2]);
        $school = School::factory()->create(['company_id' => 2]);
        $program = Program::factory()->create(['company_id' => 2]);
        $course = Course::factory()->create([
            'school_id' => $school->id,
            'program_id' => $program->id,
            'year' => 2026,
            'short_name' => 'SEP1',
        ]);
        $augustCourse = Course::factory()->create([
            'school_id' => $school->id,
            'program_id' => $program->id,
            'year' => 2026,
            'short_name' => 'AUG1',
        ]);
        $group = Group::factory()->create(['company_id' => 2]);

        Planning::create([
            'begin' => '2026-09-01 10:00:00',
            'end' => '2026-09-01 12:00:00',
            'location' => 'na',
            'group_id' => $group->id,
            'course_id' => $course->id,
        ]);
        Planning::create([
            'begin' => '2026-08-01 10:00:00',
            'end' => '2026-08-01 12:00:00',
            'location' => 'na',
            'group_id' => $group->id,
            'course_id' => $augustCourse->id,
        ]);

        $html = $this->actingAs($user)
            ->withSession([
                'current_year' => 2026,
                'current_month' => 9,
                'planning_week_start' => '2026-08-31',
            ])
            ->get(route('planning.index', ['view' => 'week']))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('SEP1', $html);
        $this->assertStringNotContainsString('AUG1', $html);
    }
}
