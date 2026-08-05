<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Group;
use App\Models\GroupCourse;
use App\Models\Program;
use App\Models\School;
use App\Models\User;
use App\Support\SchoolContext;
use Database\Seeders\CompanySeeder;
use Database\Seeders\StatusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupCourseLinkRulesTest extends TestCase
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

    private function makeSchool(string $context): School
    {
        return School::factory()->create([
            'company_id' => 2,
            'context' => $context,
        ]);
    }

    private function makeCourse(School $school): Course
    {
        $program = Program::factory()->create(['company_id' => 2]);

        return Course::factory()->create([
            'school_id' => $school->id,
            'program_id' => $program->id,
        ]);
    }

    public function test_group_index_lists_groups(): void
    {
        $user = User::factory()->create(['company_id' => 2]);

        Group::create([
            'name' => 'Visible Group',
            'short_name' => 'VG1',
            'size' => 5,
            'company_id' => 2,
            'active' => true,
            'year' => now()->format('Y'),
        ]);

        $this->actingAs($user)
            ->get(route('group.index'))
            ->assertOk()
            ->assertSee('Visible Group', false);
    }

    public function test_education_rejects_second_course_link(): void
    {
        $user = User::factory()->create(['company_id' => 2]);
        $school = $this->makeSchool(SchoolContext::EDUCATION);
        $courseA = $this->makeCourse($school);
        $courseB = $this->makeCourse($school);

        $group = Group::create([
            'name' => 'G1',
            'short_name' => 'G1X',
            'size' => 10,
            'company_id' => 2,
            'active' => true,
            'year' => now()->format('Y'),
        ]);

        GroupCourse::create([
            'group_id' => $group->id,
            'course_id' => $courseA->id,
        ]);

        $this->actingAs($user)
            ->withSession(['course_id' => $courseB->id, 'course' => $courseB->name])
            ->get(route('group.link', $group->id))
            ->assertRedirect()
            ->assertSessionHas('danger');

        $this->assertFalse(
            GroupCourse::where('group_id', $group->id)->where('course_id', $courseB->id)->exists()
        );
    }

    public function test_mentoring_allows_second_activity_link(): void
    {
        $user = User::factory()->create(['company_id' => 2]);
        $school = $this->makeSchool(SchoolContext::MENTORING);
        $courseA = $this->makeCourse($school);
        $courseB = $this->makeCourse($school);

        $group = Group::create([
            'name' => 'Student',
            'short_name' => 'STU',
            'size' => 1,
            'company_id' => 2,
            'active' => true,
            'year' => now()->format('Y'),
        ]);

        GroupCourse::create([
            'group_id' => $group->id,
            'course_id' => $courseA->id,
        ]);

        $this->actingAs($user)
            ->withSession(['course_id' => $courseB->id, 'course' => $courseB->name])
            ->get(route('group.link', $group->id))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertTrue(
            GroupCourse::where('group_id', $group->id)->where('course_id', $courseB->id)->exists()
        );
    }

    public function test_education_course_show_hides_available_groups_section(): void
    {
        config(['app.locale' => 'fr']);

        $user = User::factory()->create(['company_id' => 2]);
        $school = $this->makeSchool(SchoolContext::EDUCATION);
        $course = $this->makeCourse($school);

        $this->actingAs($user)
            ->get(route('course.show', $course->id))
            ->assertOk()
            ->assertDontSee(__('messages.groups_available'), false);
    }

    public function test_mentoring_course_show_shows_available_students_section(): void
    {
        config(['app.locale' => 'fr']);

        $user = User::factory()->create(['company_id' => 2]);
        $school = $this->makeSchool(SchoolContext::MENTORING);
        $course = $this->makeCourse($school);

        $this->actingAs($user)
            ->get(route('course.show', $course->id))
            ->assertOk()
            ->assertSee('Étudiants disponibles', false);
    }
}
