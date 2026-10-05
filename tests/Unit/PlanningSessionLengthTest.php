<?php

namespace Tests\Unit;

use App\Http\Utility\Tools;
use Tests\TestCase;

class PlanningSessionLengthTest extends TestCase
{
    public function test_planning_end_is_computed_from_course_session_length(): void
    {
        $end = Tools::planningEndFromSessionLength('2026-08-18', 10, 0, 2.5);

        $this->assertSame('2026-08-18 12:30:00', $end->format('Y-m-d H:i:s'));
    }

    public function test_planning_end_parses_comma_decimal_session_length(): void
    {
        $end = Tools::planningEndFromSessionLength('2026-08-18', 14, 0, '2,5');

        $this->assertSame('2026-08-18 16:30:00', $end->format('Y-m-d H:i:s'));
    }

    public function test_planning_gain_matches_duration_times_rate_with_default_multiplier(): void
    {
        $begin = '2026-08-18 10:00:00';
        $end = Tools::planningEndFromSessionLength('2026-08-18', 10, 0, 2.5)
            ->format('Y-m-d H:i:s');

        $this->assertSame(218.75, Tools::planningGain($begin, $end, 87.5, 1.0));
    }
}
