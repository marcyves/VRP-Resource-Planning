<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Course;
use App\Models\Group;
use App\Models\GroupCourse;
use App\Models\Planning;
use App\Models\Program;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanningCreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_planning_create_form_renders_duration_rate_and_computed_end(): void
    {
        [$user, $course] = $this->makeCreateContext([
            'session_length' => 2.5,
            'rate' => 87.5,
        ]);

        $response = $this->actingAs($user)
            ->withSession([
                'planning_create_date' => '2026-08-18',
                'planning_create_course_id' => $course->id,
                'planning_create_hour' => 10,
                'planning_create_minutes' => 0,
            ])
            ->get(route('planning.create'));

        $response->assertOk();
        $response->assertSeeInOrder([
            'planning-session-form__row--when',
            __('messages.date'),
            __('messages.begin'),
            __('messages.end'),
            'planning-session-form__row--billing-create',
            __('messages.duration_standard'),
            __('messages.hourly_rate'),
            __('messages.intervention_amount'),
            'planning-session-form__row--assignments',
            __('messages.group'),
            __('messages.course'),
        ], false);
        $response->assertSee('name="session_length"', false);
        $response->assertSee('name="end_hour"', false);
        $response->assertSee('name="end_minutes"', false);
        $response->assertDontSee('name="hourly_rate"', false);
        $response->assertDontSee('name="intervention_amount"', false);
        $response->assertSee('87,50 €/h', false);
        $response->assertSee('218,75 €', false);
        $response->assertSee($course->name, false);

        $html = $response->getContent();
        $this->assertMatchesRegularExpression('/id="end"[^>]*>[\s\S]*<option value="12"\s+selected/', $html);
        $this->assertMatchesRegularExpression('/name="end_minutes"[^>]*>[\s\S]*<option value="30"\s+selected/', $html);
        $this->assertMatchesRegularExpression('/name="session_length"[^>]*value="2,5"/', $html);
    }

    public function test_planning_create_form_uses_terminology_profile_for_group_and_course(): void
    {
        [$user, $course] = $this->makeCreateContext([
            'terminology_profile' => Company::PROFILE_CONSULTING,
        ]);

        $this->actingAs($user)
            ->withSession([
                'planning_create_date' => '2026-08-18',
                'planning_create_course_id' => $course->id,
                'planning_create_hour' => 10,
                'planning_create_minutes' => 0,
            ])
            ->get(route('planning.create'))
            ->assertOk()
            ->assertSeeInOrder([
                'planning-session-form__row--assignments',
                __('messages.group'),
                __('messages.course'),
            ], false)
            ->assertSee('Équipe', false)
            ->assertSee('Phase', false);
    }

    public function test_planning_store_uses_submitted_end_time_over_standard_duration(): void
    {
        [$user, $course, $group] = $this->makeCreateContext();

        $this->actingAs($user)
            ->withSession([
                'planning_create_date' => '2026-08-18',
                'planning_create_course_id' => $course->id,
                'course_id' => $course->id,
            ])
            ->post(route('planning.store'), [
                'date' => '2026-08-18',
                'hour' => 10,
                'minutes' => 0,
                'end_hour' => 13,
                'end_minutes' => 15,
                'session_length' => '2',
                'course' => $course->id,
                'group' => $group->id,
            ])
            ->assertRedirect(route('planning.index'));

        $planning = Planning::query()->first();

        $this->assertNotNull($planning);
        $this->assertSame('2026-08-18 10:00:00', $planning->begin);
        $this->assertSame('2026-08-18 13:15:00', $planning->end);
        $this->assertSame($group->id, $planning->group_id);
        $this->assertSame($course->id, $planning->course_id);
    }

    public function test_start_create_then_form_shows_computed_end(): void
    {
        [$user, $course] = $this->makeCreateContext([
            'session_length' => 3.0,
            'rate' => 100,
        ]);

        $this->actingAs($user)
            ->withSession(['course_id' => $course->id])
            ->post(route('planning.create.start'), [
                'date' => '2026-08-18',
                'hour' => 9,
                'minutes' => 0,
                'course' => $course->id,
            ])
            ->assertRedirect(route('planning.create'));

        $html = $this->actingAs($user)
            ->get(route('planning.create'))
            ->assertOk()
            ->assertSee(__('messages.duration_standard'), false)
            ->assertSee('300,00 €', false)
            ->getContent();

        $this->assertMatchesRegularExpression('/id="begin-hour"[^>]*>[\s\S]*<option value="9"\s+selected/', $html);
        $this->assertMatchesRegularExpression('/id="end"[^>]*>[\s\S]*<option value="12"\s+selected/', $html);
    }

    public function test_planning_store_computes_end_from_standard_duration_when_end_is_invalid(): void
    {
        [$user, $course, $group] = $this->makeCreateContext(['session_length' => 2.5]);

        $this->actingAs($user)
            ->post(route('planning.store'), [
                'date' => '2026-08-18',
                'hour' => 10,
                'minutes' => 0,
                'end_hour' => 10,
                'end_minutes' => 0,
                'session_length' => '2,5',
                'course' => $course->id,
                'group' => $group->id,
            ])
            ->assertRedirect(route('planning.index'));

        $planning = Planning::query()->first();

        $this->assertNotNull($planning);
        $this->assertSame('2026-08-18 10:00:00', $planning->begin);
        $this->assertSame('2026-08-18 12:30:00', $planning->end);
    }

    /**
     * @param  array{session_length?: float, rate?: float, terminology_profile?: string}  $options
     * @return array{0: User, 1: Course, 2: Group, 3: School}
     */
    private function makeCreateContext(array $options = []): array
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
            'name' => 'Course create test',
            'short_name' => 'CCT',
            'rate' => $options['rate'] ?? 87.5,
            'session_length' => $options['session_length'] ?? 2.0,
        ]);
        $group = Group::query()->create([
            'name' => 'Group test',
            'short_name' => 'GT',
            'company_id' => $user->company_id,
            'size' => 15,
            'active' => true,
            'year' => 2026,
        ]);
        GroupCourse::create([
            'group_id' => $group->id,
            'course_id' => $course->id,
        ]);

        return [$user, $course, $group, $school];
    }
}
