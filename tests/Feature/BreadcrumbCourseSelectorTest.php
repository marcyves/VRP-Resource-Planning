<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Program;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BreadcrumbCourseSelectorTest extends TestCase
{
    use RefreshDatabase;

    public function test_planning_breadcrumb_lists_courses_regardless_of_agenda_year(): void
    {
        $user = User::factory()->create();
        $school = $this->makeSchool($user, 'Test School');
        $program = Program::factory()->create(['company_id' => $user->company_id]);

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

    public function test_planning_breadcrumb_omits_schools_without_usable_courses(): void
    {
        $user = User::factory()->create();
        $program = Program::factory()->create(['company_id' => $user->company_id]);

        $usable = $this->makeSchool($user, 'Usable Client');
        $this->makeSchool($user, 'Empty Client');
        $archivedOnly = $this->makeSchool($user, 'Archived Client');

        Course::factory()->create([
            'school_id' => $usable->id,
            'program_id' => $program->id,
            'name' => 'Live Course',
        ]);
        Course::factory()->archived()->create([
            'school_id' => $archivedOnly->id,
            'program_id' => $program->id,
            'name' => 'Only Archived Course',
        ]);

        $html = $this->actingAs($user)
            ->withSession([
                'school_id' => $usable->id,
                'school' => $usable->name,
            ])
            ->get(route('planning.index'))
            ->assertOk()
            ->getContent();

        $schoolOptions = $this->selectOptionLabels($html, 'breadcrumb-school');

        $this->assertContains('Usable Client', $schoolOptions);
        $this->assertNotContains('Empty Client', $schoolOptions);
        $this->assertNotContains('Archived Client', $schoolOptions);
    }

    public function test_planning_breadcrumb_omits_archived_courses_and_sorts_the_rest(): void
    {
        $user = User::factory()->create();
        $school = $this->makeSchool($user, 'Sort School');
        $programA = Program::factory()->create([
            'company_id' => $user->company_id,
            'name' => 'Program A',
            'short_description' => 'PA',
        ]);
        $programB = Program::factory()->create([
            'company_id' => $user->company_id,
            'name' => 'Program B',
            'short_description' => 'PB',
        ]);

        Course::factory()->create([
            'school_id' => $school->id,
            'program_id' => $programA->id,
            'year' => '2026',
            'semester' => '1',
            'name' => 'Zebra Course',
        ]);
        Course::factory()->create([
            'school_id' => $school->id,
            'program_id' => $programA->id,
            'year' => '2025',
            'semester' => '1',
            'name' => 'Mid Course',
        ]);
        Course::factory()->create([
            'school_id' => $school->id,
            'program_id' => $programB->id,
            'year' => '2026',
            'semester' => '1',
            'name' => 'Alpha Course',
        ]);
        Course::factory()->archived()->create([
            'school_id' => $school->id,
            'program_id' => $programA->id,
            'year' => '2024',
            'semester' => '1',
            'name' => 'Archived Course',
        ]);

        $html = $this->actingAs($user)
            ->withSession([
                'school_id' => $school->id,
                'school' => $school->name,
            ])
            ->get(route('planning.index'))
            ->assertOk()
            ->getContent();

        $courseOptions = $this->selectOptionLabels($html, 'breadcrumb-course');

        $this->assertSame(
            [
                __('messages.course'),
                '(PA) Mid Course',
                '(PA) Zebra Course',
                '(PB) Alpha Course',
            ],
            $courseOptions
        );
        $this->assertNotContains('(PA) Archived Course', $courseOptions);
    }

    public function test_school_page_still_lists_archived_courses(): void
    {
        $user = User::factory()->create();
        $school = $this->makeSchool($user, 'Archive Admin School');
        $program = Program::factory()->create(['company_id' => $user->company_id]);

        Course::factory()->archived()->create([
            'school_id' => $school->id,
            'program_id' => $program->id,
            'name' => 'Archived On School Page',
            'year' => now()->format('Y'),
        ]);

        $this->actingAs($user)
            ->withSession(['current_year' => now()->format('Y')])
            ->get(route('school.show', $school))
            ->assertOk()
            ->assertSee('Archived On School Page', false);
    }

    private function makeSchool(User $user, string $name): School
    {
        return School::query()->create([
            'name' => $name,
            'company_id' => $user->company_id,
        ]);
    }

    /**
     * @return list<string>
     */
    private function selectOptionLabels(string $html, string $selectId): array
    {
        $this->assertTrue(
            (bool) preg_match(
                '/<select[^>]*id="'.preg_quote($selectId, '/').'"[^>]*>(.*?)<\/select>/s',
                $html,
                $select
            ),
            "Missing select #{$selectId}"
        );

        preg_match_all('/<option[^>]*>(.*?)<\/option>/s', $select[1], $options);

        return array_map(
            fn (string $label) => trim(html_entity_decode(strip_tags($label))),
            $options[1]
        );
    }
}
