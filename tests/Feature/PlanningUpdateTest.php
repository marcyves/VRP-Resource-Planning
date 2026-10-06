<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Course;
use App\Models\Group;
use App\Models\Planning;
use App\Models\Program;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanningUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_planning_edit_form_submits_update_before_duplicate_actions(): void
    {
        [$user, $planning] = $this->makePlanningContext();

        $response = $this->actingAs($user)->get(route('planning.edit', $planning->id));

        $response->assertOk();
        $response->assertSeeInOrder([
            'planning-session-form',
            __('messages.plan'),
            'planning-duplicate-actions',
        ], false);
        $response->assertSee('data-planning-duplicate-open', false);
        $response->assertSee('planning-duplicate-dialog', false);

        $html = $response->getContent();
        $sessionFormPos = strpos($html, 'planning-session-form');
        $sessionFormClose = strpos($html, '</form>', (int) $sessionFormPos);
        $duplicatePos = strpos($html, 'planning-duplicate-actions');

        $this->assertNotFalse($sessionFormPos);
        $this->assertNotFalse($sessionFormClose);
        $this->assertNotFalse($duplicatePos);
        $this->assertGreaterThan($sessionFormClose, $duplicatePos, 'Duplicate actions must be outside the update form.');
    }

    public function test_planning_edit_form_renders_when_billing_and_assignment_rows_in_order(): void
    {
        [$user, $planning] = $this->makePlanningContext(['rate' => 87.5, 'billable_rate' => 120]);

        $response = $this->actingAs($user)->get(route('planning.edit', $planning->id));

        $response->assertOk();
        $response->assertSeeInOrder([
            'planning-session-form__row--when',
            __('messages.date'),
            __('messages.begin'),
            __('messages.end'),
            'planning-session-form__row--billing',
            __('messages.duration_indicative'),
            __('messages.hourly_rate'),
            __('messages.billable_rate'),
            __('messages.billed_amount'),
            'planning-session-form__row--assignments',
            __('messages.group'),
            __('messages.course'),
        ], false);
        $response->assertSee('87,50 €/h', false);
        $response->assertSee('name="billable_rate"', false);
        $response->assertDontSee('name="hourly_rate"', false);
        $response->assertDontSee('name="billed_amount"', false);
    }

    public function test_planning_edit_course_dropdown_omits_archived_and_empty_school_courses(): void
    {
        [$user, $planning, , $course, $school] = $this->makePlanningContext();
        $program = $course->program;

        $sortedLater = Course::factory()->create([
            'school_id' => $school->id,
            'program_id' => $program->id,
            'year' => '2027',
            'semester' => '1',
            'name' => 'Later Live Course',
        ]);
        Course::factory()->archived()->create([
            'school_id' => $school->id,
            'program_id' => $program->id,
            'name' => 'Archived Edit Course',
        ]);
        $emptySchool = School::query()->create([
            'name' => 'Empty Dropdown School',
            'company_id' => $user->company_id,
        ]);

        $html = $this->actingAs($user)
            ->get(route('planning.edit', $planning->id))
            ->assertOk()
            ->getContent();

        $this->assertTrue(
            (bool) preg_match('/<select[^>]*id="course_id"[^>]*>(.*?)<\/select>/s', $html, $select),
            'Missing course dropdown on planning edit'
        );

        $this->assertStringContainsString('value="'.$course->id.'"', $select[1]);
        $this->assertStringContainsString('value="'.$sortedLater->id.'"', $select[1]);
        $this->assertStringContainsString('Course test', $select[1]);
        $this->assertStringContainsString('Later Live Course', $select[1]);
        $this->assertStringNotContainsString('Archived Edit Course', $select[1]);
        $this->assertStringNotContainsString('Empty Dropdown School', $select[1]);

        preg_match_all('/<option[^>]*>(.*?)<\/option>/s', $select[1], $options);
        $labels = array_map(fn (string $label) => trim(strip_tags($label)), $options[1]);
        $this->assertSame(['Course test', 'Later Live Course'], $labels);
    }

    public function test_planning_edit_form_uses_terminology_profile_for_group_and_course(): void
    {
        [$user, $planning] = $this->makePlanningContext([
            'terminology_profile' => Company::PROFILE_CONSULTING,
        ]);

        $this->actingAs($user)
            ->get(route('planning.edit', $planning->id))
            ->assertOk()
            ->assertSeeInOrder([
                'planning-session-form__row--assignments',
                __('messages.group'),
                __('messages.course'),
            ], false)
            ->assertSee('Équipe', false)
            ->assertSee('Phase', false);
    }

    public function test_planning_update_persists_changes(): void
    {
        [$user, $planning, $group, $course, $school] = $this->makePlanningContext();

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
            ->assertRedirect(route('school.show', $school->id).'#billing');

        $planning->refresh();

        $this->assertSame('2026-06-23 14:00:00', $planning->begin);
        $this->assertSame('2026-06-23 16:30:00', $planning->end);
        $this->assertSame(150, (int) $planning->billable_rate);
    }

    /**
     * @param  array{rate?: float, billable_rate?: int|float, terminology_profile?: string}  $options
     * @return array{0: User, 1: Planning, 2: Group, 3: Course, 4: School}
     */
    private function makePlanningContext(array $options = []): array
    {
        $user = User::factory()->create();

        if (isset($options['terminology_profile'])) {
            $user->company->update([
                'terminology_profile' => $options['terminology_profile'],
            ]);
        }

        $school = School::query()->create([
            'name' => 'School test',
            'company_id' => $user->company_id,
        ]);
        $program = Program::factory()->create(['company_id' => $user->company_id]);
        $course = Course::factory()->create([
            'school_id' => $school->id,
            'program_id' => $program->id,
            'name' => 'Course test',
            'short_name' => 'CT',
            'rate' => $options['rate'] ?? 87.5,
        ]);
        $group = Group::query()->create([
            'name' => 'Group test',
            'short_name' => 'GT',
            'company_id' => $user->company_id,
            'size' => 15,
            'active' => true,
            'year' => 2026,
        ]);

        $planning = Planning::create([
            'begin' => '2026-06-22 10:00:00',
            'end' => '2026-06-22 12:00:00',
            'location' => 'na',
            'group_id' => $group->id,
            'course_id' => $course->id,
            'billable_rate' => $options['billable_rate'] ?? 120,
        ]);

        return [$user, $planning, $group, $course, $school];
    }
}
