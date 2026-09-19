<?php

namespace Tests\Unit;

use App\Http\Utility\Tools;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Tests\TestCase;

class PlanningViewHelperTest extends TestCase
{
    public function test_planning_view_defaults_to_month(): void
    {
        $this->assertSame('month', Tools::getPlanningView(Request::create('/planning', 'GET')));
    }

    public function test_planning_view_accepts_week_query(): void
    {
        $request = Request::create('/planning', 'GET', ['view' => 'week']);

        $this->assertSame('week', Tools::getPlanningView($request));
        $this->assertSame('week', session('planning_view'));
    }

    public function test_shift_week_goes_back_seven_days(): void
    {
        $result = Tools::shiftPlanningPeriod('week', Carbon::parse('2026-08-17'), 2026, 8, -1);

        $this->assertSame('2026-08-10', $result['week_start']->toDateString());
        $this->assertSame(2026, $result['year']);
        $this->assertSame(8, $result['month']);
    }

    public function test_shift_week_across_month_updates_month(): void
    {
        $weekStart = Carbon::parse('2026-08-31')->startOfWeek(Carbon::MONDAY);
        $result = Tools::shiftPlanningPeriod('week', $weekStart, 2026, 9, 1);

        $this->assertSame('2026-09-07', $result['week_start']->toDateString());
        $this->assertSame(9, $result['month']);
    }

    public function test_shift_month_from_november_goes_to_december(): void
    {
        $result = Tools::shiftPlanningPeriod('month', Carbon::parse('2026-11-02'), 2026, 11, 1);

        $this->assertSame(12, $result['month']);
        $this->assertSame(2026, $result['year']);
    }

    public function test_week_event_position_places_morning_block(): void
    {
        $slot = Tools::weekEventPosition(
            Carbon::parse('2026-08-17 10:00:00'),
            Carbon::parse('2026-08-17 12:00:00')
        );

        $this->assertTrue($slot['visible']);
        $this->assertEqualsWithDelta(100 / 6, $slot['top'], 0.01);
        $this->assertEqualsWithDelta(100 / 6, $slot['height'], 0.01);
    }

    public function test_week_event_position_starts_at_eight(): void
    {
        $slot = Tools::weekEventPosition(
            Carbon::parse('2026-08-17 08:00:00'),
            Carbon::parse('2026-08-17 09:00:00')
        );

        $this->assertTrue($slot['visible']);
        $this->assertEqualsWithDelta(0, $slot['top'], 0.01);
        $this->assertEqualsWithDelta(100 / 12, $slot['height'], 0.01);
    }

    public function test_week_event_position_clamps_to_agenda_bounds(): void
    {
        $early = Tools::weekEventPosition(
            Carbon::parse('2026-08-17 07:00:00'),
            Carbon::parse('2026-08-17 09:00:00')
        );
        $late = Tools::weekEventPosition(
            Carbon::parse('2026-08-17 19:30:00'),
            Carbon::parse('2026-08-17 21:00:00')
        );
        $outside = Tools::weekEventPosition(
            Carbon::parse('2026-08-17 21:00:00'),
            Carbon::parse('2026-08-17 22:00:00')
        );

        $this->assertTrue($early['visible']);
        $this->assertEqualsWithDelta(0, $early['top'], 0.01);
        $this->assertEqualsWithDelta(100 / 12, $early['height'], 0.01);

        $this->assertTrue($late['visible']);
        $this->assertEqualsWithDelta(100 * 11.5 / 12, $late['top'], 0.01);
        $this->assertEqualsWithDelta(100 * 0.5 / 12, $late['height'], 0.01);

        $this->assertFalse($outside['visible']);
    }
}
