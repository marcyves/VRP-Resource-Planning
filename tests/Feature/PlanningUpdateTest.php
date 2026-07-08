<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Group;
use App\Models\Planning;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanningUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_planning_edit_form_submits_update_before_duplicate_actions(): void
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
            'billable_rate' => 120,
        ]);

        $response = $this->actingAs($user)->get(route('planning.edit', $planning->id));

        $response->assertOk();
        $response->assertSeeInOrder([
            'planning-session-form',
            __('messages.plan'),
            'planning-duplicate-actions',
        ], false);

        $updatePos = strpos($response->getContent(), 'planning.update');
        $duplicatePos = strpos($response->getContent(), 'planning-duplicate-actions');
        $formClosePos = strpos($response->getContent(), '</form>');

        $this->assertNotFalse($updatePos);
        $this->assertNotFalse($duplicatePos);
        $this->assertNotFalse($formClosePos);
        $this->assertLessThan($formClosePos, $duplicatePos, 'Duplicate actions must be outside the update form.');
    }

    public function test_planning_update_persists_changes(): void
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
            'billable_rate' => 120,
        ]);

        $this->actingAs($user)
            ->put(route('planning.update', $planning->id), [
                'day' => 23,
                'month' => 6,
                'year' => 2026,
                'hour' => 14,
                'minutes' => 0,
                'end_hour' => 16,
                'end_minutes' => 30,
                'group_id' => $group->id,
                'course_id' => $course->id,
                'billable_rate' => 150,
            ])
            ->assertRedirect(route('planning.index'));

        $planning->refresh();

        $this->assertSame('2026-06-23 14:00:00', $planning->begin);
        $this->assertSame('2026-06-23 16:30:00', $planning->end);
        $this->assertSame(150, (int) $planning->billable_rate);
    }
}
