<?php

namespace Tests\Unit;

use App\Http\Utility\Tools;
use Tests\TestCase;

class BillingInformationTest extends TestCase
{
    public function test_billing_schedule_includes_line_gain(): void
    {
        $planning = collect([
            (object) [
                'planning_id' => 42,
                'school_id' => 1,
                'school_name' => 'Test School',
                'course_id' => 10,
                'course_name' => 'Python',
                'group_name' => 'G1',
                'begin' => '2026-08-05 09:00:00',
                'end' => '2026-08-05 12:00:00',
                'rate' => 100.0,
                'billable_rate' => 1.0,
                'invoice_id' => null,
                'session_length' => 3.0,
            ],
        ]);

        [$schools] = Tools::getBillingInformation($planning);
        $school = reset($schools);
        $course = reset($school['courses']);
        $schedule = reset($course['schedule']);

        $this->assertSame(300.0, $schedule['gain']);
        $this->assertSame(360.0, $schedule['gain_ttc']);
    }
}
