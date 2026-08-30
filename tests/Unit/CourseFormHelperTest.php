<?php

namespace Tests\Unit;

use App\Http\Utility\Tools;
use Tests\TestCase;

class CourseFormHelperTest extends TestCase
{
    public function test_course_total_hours_multiplies_sessions_by_length(): void
    {
        $this->assertSame(30.0, Tools::courseTotalHours(10, 3));
        $this->assertSame(17.5, Tools::courseTotalHours('5', '3,5'));
    }

    public function test_hourly_rate_ht_keeps_ht_amount(): void
    {
        $this->assertSame(87.5, Tools::hourlyRateHt('87,50', 'ht'));
    }

    public function test_hourly_rate_ht_converts_ttc_by_vat(): void
    {
        $this->assertSame(100.0, Tools::hourlyRateHt(120, 'ttc'));
        $this->assertSame(83.3333, Tools::hourlyRateHt(100, 'ttc'));
    }

    public function test_hourly_rate_ht_defaults_to_ttc(): void
    {
        $this->assertSame(100.0, Tools::hourlyRateHt(120, 'unknown'));
        $this->assertSame(100.0, Tools::hourlyRateHt(120));
    }

    public function test_hourly_rate_ttc_from_stored_ht(): void
    {
        $this->assertSame(105.0, Tools::hourlyRateTtc(87.5));
    }

    public function test_hourly_rate_ttc_round_trips_after_ttc_input(): void
    {
        foreach ([33.33, 100.0, 120.0, 45.5, 87.5] as $ttc) {
            $ht = Tools::hourlyRateHt($ttc, 'ttc');
            $this->assertSame(
                round($ttc, 2),
                Tools::hourlyRateTtc($ht),
                "TTC round-trip failed for {$ttc}"
            );
        }
    }

    public function test_default_course_semester_is_one_from_january_to_june(): void
    {
        $this->assertSame('1', Tools::defaultCourseSemester(1));
        $this->assertSame('1', Tools::defaultCourseSemester(6));
    }

    public function test_default_course_semester_is_two_from_july_to_december(): void
    {
        $this->assertSame('2', Tools::defaultCourseSemester(7));
        $this->assertSame('2', Tools::defaultCourseSemester(12));
    }
}
