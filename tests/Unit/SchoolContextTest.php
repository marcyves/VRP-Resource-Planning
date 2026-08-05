<?php

namespace Tests\Unit;

use App\Models\School;
use App\Support\SchoolContext;
use Tests\TestCase;

class SchoolContextTest extends TestCase
{
    public function test_education_school_uses_messages_locale(): void
    {
        config(['app.locale' => 'fr']);

        $school = new School(['context' => SchoolContext::EDUCATION]);

        $this->assertSame(__('messages.course'), SchoolContext::msg($school, 'course'));
        $this->assertSame(__('messages.group'), SchoolContext::msg($school, 'group'));
        $this->assertFalse(SchoolContext::isMentoring($school));
    }

    public function test_mentoring_school_uses_overlay(): void
    {
        config(['app.locale' => 'fr']);

        $school = new School(['context' => SchoolContext::MENTORING]);

        $this->assertSame('Activité', SchoolContext::msg($school, 'course'));
        $this->assertSame('Étudiant', SchoolContext::msg($school, 'group'));
        $this->assertSame('Parcours', SchoolContext::msg($school, 'program'));
        $this->assertTrue(SchoolContext::isMentoring($school));
    }

    public function test_null_school_falls_back_to_messages(): void
    {
        config(['app.locale' => 'fr']);

        $this->assertSame(__('messages.course'), SchoolContext::msg(null, 'course'));
        $this->assertFalse(SchoolContext::isMentoring(null));
    }

    public function test_mentoring_falls_back_when_overlay_key_missing(): void
    {
        config(['app.locale' => 'fr']);

        $school = new School(['context' => SchoolContext::MENTORING]);

        $this->assertSame(__('messages.sessions'), SchoolContext::msg($school, 'sessions'));
    }

    public function test_school_model_helpers(): void
    {
        config(['app.locale' => 'fr']);

        $mentoring = new School(['context' => SchoolContext::MENTORING]);
        $education = new School(['context' => SchoolContext::EDUCATION]);

        $this->assertTrue($mentoring->isMentoring());
        $this->assertFalse($education->isMentoring());
        $this->assertSame(__('messages.school_context_mentoring'), $mentoring->contextLabel());
        $this->assertSame(__('messages.school_context_education'), $education->contextLabel());
    }

    public function test_allows_multi_course_link_only_for_mentoring(): void
    {
        $mentoring = new School(['context' => SchoolContext::MENTORING]);
        $education = new School(['context' => SchoolContext::EDUCATION]);

        $this->assertTrue(SchoolContext::allowsMultiCourseLink($mentoring));
        $this->assertFalse(SchoolContext::allowsMultiCourseLink($education));
        $this->assertFalse(SchoolContext::allowsMultiCourseLink(null));
    }
}
