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

class CourseFormTest extends TestCase
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

    public function test_create_form_shows_volume_line_and_ttc_rate_default(): void
    {
        [$user, $school] = $this->makeUserAndSchool();

        $this->actingAs($user)
            ->get(route('course.create', $school->id))
            ->assertOk()
            ->assertSee('course-volume-line', false)
            ->assertSee(__('messages.sessions_of'), false)
            ->assertSee('name="rate_basis" value="ttc"', false)
            ->assertSee('checked', false);
    }

    public function test_store_converts_ttc_rate_to_ht(): void
    {
        [$user, $school, $program] = $this->makeUserAndSchool();

        $this->actingAs($user)
            ->post(route('course.store', $school->id), $this->coursePayload($program->id, [
                'rate' => '120',
                'rate_basis' => 'ttc',
            ]))
            ->assertRedirect(route('dashboard'));

        $this->assertSame(100.0, (float) Course::query()->where('school_id', $school->id)->value('rate'));
    }

    public function test_store_keeps_ht_rate(): void
    {
        [$user, $school, $program] = $this->makeUserAndSchool();

        $this->actingAs($user)
            ->post(route('course.store', $school->id), $this->coursePayload($program->id, [
                'rate' => '87,50',
                'rate_basis' => 'ht',
            ]))
            ->assertRedirect(route('dashboard'));

        $this->assertSame(87.5, (float) Course::query()->where('school_id', $school->id)->value('rate'));
    }

    public function test_edit_form_shows_ttc_amount_by_default(): void
    {
        [$user, $school, $program] = $this->makeUserAndSchool();
        $course = Course::factory()->create([
            'school_id' => $school->id,
            'program_id' => $program->id,
            'rate' => 87.5,
            'sessions' => 10,
            'session_length' => 3,
        ]);

        $this->actingAs($user)
            ->get(route('course.edit', $course->id))
            ->assertOk()
            ->assertSee('course-volume-line', false)
            ->assertSee('value="105"', false)
            ->assertSee('name="rate_basis" value="ttc"', false);
    }

    public function test_update_converts_ttc_rate_to_ht(): void
    {
        [$user, $school, $program] = $this->makeUserAndSchool();
        $course = Course::factory()->create([
            'school_id' => $school->id,
            'program_id' => $program->id,
            'rate' => 50,
        ]);

        $this->actingAs($user)
            ->put(route('course.update', $course->id), $this->coursePayload($program->id, [
                'name' => $course->name,
                'short_name' => $course->short_name,
                'rate' => '240',
                'rate_basis' => 'ttc',
            ]))
            ->assertRedirect(route('dashboard'));

        $this->assertSame(200.0, (float) $course->fresh()->rate);
    }

    public function test_store_and_edit_preserve_ttc_rate_after_round_trip(): void
    {
        [$user, $school, $program] = $this->makeUserAndSchool();

        $this->actingAs($user)
            ->post(route('course.store', $school->id), $this->coursePayload($program->id, [
                'rate' => '33,33',
                'rate_basis' => 'ttc',
            ]))
            ->assertRedirect(route('dashboard'));

        $course = Course::query()->where('school_id', $school->id)->firstOrFail();
        $this->assertSame(27.775, (float) $course->rate);

        $this->actingAs($user)
            ->get(route('course.edit', $course->id))
            ->assertOk()
            ->assertSee('value="33.33"', false);
    }

    /**
     * @return array{0: User, 1: School, 2: Program}
     */
    private function makeUserAndSchool(): array
    {
        $user = User::factory()->create(['company_id' => 2]);
        $school = School::factory()->create(['company_id' => 2]);
        $program = Program::factory()->create(['company_id' => 2]);

        return [$user, $school, $program];
    }

    private function coursePayload(int $programId, array $overrides = []): array
    {
        return array_merge([
            'name' => 'Python avancé',
            'short_name' => 'PYA',
            'program_id' => $programId,
            'sessions' => '10',
            'session_length' => '3',
            'year' => '2026',
            'semester' => 'S1',
            'rate' => '120',
            'rate_basis' => 'ttc',
        ], $overrides);
    }
}
