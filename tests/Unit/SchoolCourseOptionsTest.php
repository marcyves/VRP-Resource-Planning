<?php

namespace Tests\Unit;

use App\Models\Course;
use App\Models\Program;
use App\Models\School;
use App\Models\User;
use App\Support\SchoolCourseOptions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchoolCourseOptionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_schools_omit_empty_and_archived_only_clients(): void
    {
        $user = User::factory()->create();
        $program = Program::factory()->create(['company_id' => $user->company_id]);

        $usable = $this->makeSchool($user, 'Usable School');
        $this->makeSchool($user, 'Empty School');
        $archivedOnly = $this->makeSchool($user, 'Archived Only School');

        Course::factory()->create([
            'school_id' => $usable->id,
            'program_id' => $program->id,
            'name' => 'Live Course',
        ]);
        Course::factory()->archived()->create([
            'school_id' => $archivedOnly->id,
            'program_id' => $program->id,
            'name' => 'Old Course',
        ]);

        $schools = SchoolCourseOptions::schoolsFor($user);

        $this->assertSame(['Usable School'], $schools->pluck('name')->all());
        $this->assertFalse($schools->contains('name', 'Empty School'));
        $this->assertFalse($schools->contains('id', $archivedOnly->id));
    }

    public function test_courses_omit_archived_and_follow_list_order(): void
    {
        $user = User::factory()->create();
        $school = $this->makeSchool($user, 'Alpha School');
        $programA = Program::factory()->create(['company_id' => $user->company_id, 'name' => 'Program A']);
        $programB = Program::factory()->create(['company_id' => $user->company_id, 'name' => 'Program B']);

        $laterName = Course::factory()->create([
            'school_id' => $school->id,
            'program_id' => $programA->id,
            'year' => '2026',
            'semester' => '1',
            'name' => 'Zebra',
        ]);
        $earlierYear = Course::factory()->create([
            'school_id' => $school->id,
            'program_id' => $programA->id,
            'year' => '2025',
            'semester' => '1',
            'name' => 'Mid',
        ]);
        $sameYearLaterProgram = Course::factory()->create([
            'school_id' => $school->id,
            'program_id' => $programB->id,
            'year' => '2026',
            'semester' => '1',
            'name' => 'Alpha',
        ]);
        $archived = Course::factory()->archived()->create([
            'school_id' => $school->id,
            'program_id' => $programA->id,
            'year' => '2024',
            'semester' => '1',
            'name' => 'Archived Course',
        ]);

        $courses = SchoolCourseOptions::coursesForSchool($school, $user);

        $this->assertSame(
            [$earlierYear->id, $laterName->id, $sameYearLaterProgram->id],
            $courses->pluck('id')->all()
        );
        $this->assertFalse($courses->contains('id', $archived->id));
    }

    public function test_courses_can_keep_the_currently_selected_archived_course(): void
    {
        $user = User::factory()->create();
        $school = $this->makeSchool($user, 'Current School');
        $program = Program::factory()->create(['company_id' => $user->company_id]);

        $live = Course::factory()->create([
            'school_id' => $school->id,
            'program_id' => $program->id,
            'name' => 'Live',
        ]);
        $archived = Course::factory()->archived()->create([
            'school_id' => $school->id,
            'program_id' => $program->id,
            'name' => 'Archived Current',
        ]);

        $courses = SchoolCourseOptions::coursesForSchool($school, $user, $archived->id);

        $this->assertTrue($courses->contains('id', $live->id));
        $this->assertTrue($courses->contains('id', $archived->id));
    }

    private function makeSchool(User $user, string $name): School
    {
        return School::query()->create([
            'name' => $name,
            'company_id' => $user->company_id,
        ]);
    }
}
