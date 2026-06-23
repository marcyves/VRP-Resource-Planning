<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Group;
use App\Models\Planning;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanningDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_planning_delete_removes_session(): void
    {
        $user = User::factory()->create();
        $school = School::factory()->create(['company_id' => $user->company_id]);
        $course = Course::factory()->create(['school_id' => $school->id]);
        $group = Group::factory()->create(['company_id' => $user->company_id]);

        $planning = Planning::create([
            'begin' => '2026-06-22 10:00:00',
            'end' => '2026-06-22 12:00:00',
            'location' => 'na',
            'group_id' => $group->id,
            'course_id' => $course->id,
        ]);

        $response = $this->actingAs($user)->delete(route('planning.delete', $planning->id));

        $response->assertRedirect(route('planning.index'));
        $this->assertDatabaseMissing('plannings', ['id' => $planning->id]);
    }

    public function test_planning_index_renders_delete_button_with_alpine_handler(): void
    {
        $user = User::factory()->create();
        $school = School::factory()->create(['company_id' => $user->company_id]);
        $course = Course::factory()->create(['school_id' => $school->id]);
        $group = Group::factory()->create(['company_id' => $user->company_id]);

        Planning::create([
            'begin' => now()->format('Y-m-d') . ' 10:00:00',
            'end' => now()->format('Y-m-d') . ' 12:00:00',
            'location' => 'na',
            'group_id' => $group->id,
            'course_id' => $course->id,
        ]);

        $response = $this->actingAs($user)->get(route('planning.index'));

        $response->assertOk();
        $response->assertSee('data-planning-delete', false);
        $response->assertSee('planning-delete-dialog', false);
        $response->assertSee('name="_method" value="DELETE"', false);
    }
}
