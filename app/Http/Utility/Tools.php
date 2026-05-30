<?php

namespace App\Http\Utility;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Planning;

class Tools
{
    public static function getCurrentDay(Request $request)
    {
        if (isset($request->current_day)) {
            $current_day = $request->current_day + 1;
            session(['current_day' => $current_day]);
        } else {
            $current_day = session('current_day');
            if (!isset($current_day)) {
                $current_day = now()->format('d');
            }
        }
        return $current_day;
    }

    public static function getCurrentYear(Request $request)
    {

        if (isset($request->current_year)) {
            $current_year = $request->current_year;
            session(['current_year' => $current_year]);
        } else {
            $current_year = session('current_year');
            if (!isset($current_year)) {
                $current_year = now()->format('Y');
            }
        }

        return (int) $current_year;
    }

    public static function getCurrentMonth(Request $request)
    {
        if (isset($request->current_month)) {
            $current_month = $request->current_month + 1;
            session(['current_month' => $current_month]);
        } else {
            $current_month = session('current_month');
            if (!isset($current_month)) {
                $current_month = now()->format('m');
            }
        }

        return (int) $current_month;
    }

    public static function getBillingYear(Request $request): int
    {
        if (isset($request->billing_year)) {
            session(['billing_year' => (int) $request->billing_year]);
        }

        $billingYear = session('billing_year');
        if (! isset($billingYear)) {
            $billingYear = (int) now()->format('Y');
            session(['billing_year' => $billingYear]);
        }

        return (int) $billingYear;
    }

    public static function getCurrentSemester(Request $request)
    {
        if (isset($request->current_semester)) {
            $current_semester = $request->current_semester;
            session(['current_semester' => $current_semester]);
        } else {
            $current_semester = session('current_semester');
            if (!isset($current_semester)) {
                $current_semester = "all";
            }
        }

        return $current_semester;
    }

    public static function getMonthNames()
    {
        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[] = ucfirst(Carbon::parse(mktime(0, 0, 0, $m, 1, date('Y')))->translatedFormat('F'));
        }

        return $months;
    }

    public static function sessionDurationHours($begin, $end): float
    {
        return intval((strtotime($end) - strtotime($begin)) / 60) / 60;
    }

    public static function billableMultiplier(float $billableRate, float $courseRate): float
    {
        $multiplier = $billableRate <= 0 ? 1.0 : $billableRate;

        // Calendar import stored the course rate in billable_rate by mistake.
        if ($multiplier > 1 && abs($multiplier - $courseRate) < 0.001) {
            return 1.0;
        }

        if ($multiplier > 1) {
            return $multiplier / 100;
        }

        return $multiplier;
    }

    public static function billingPeriodBounds(int $year, int $month): array
    {
        $startDate = trim((string) $year).'-'.substr('0'.trim((string) $month), -2).'-0 00:00:00';
        $nextMonth = $month + 1;
        $endYear = $year;

        if ($nextMonth > 12) {
            $nextMonth = 1;
            $endYear++;
        }

        $endDate = trim((string) $endYear).'-'.substr('0'.trim((string) $nextMonth), -2).'-0 00:00:00';

        return [$startDate, $endDate];
    }

    public static function planningGain($begin, $end, $rate, $billableRate): float
    {
        $rate = (float) $rate;

        return self::sessionDurationHours($begin, $end)
            * $rate
            * self::billableMultiplier((float) $billableRate, $rate);
    }

    public static function getBillingInformation($planning)
    {
        $current_school = "";
        $course_name = "";
        $course_id = 0;
        $course_hours  = 0;
        $course_gain   = 0;

        $monthly_hours = 0;
        $monthly_gain  = 0;
        $schools = array();
        $schedules = array();

        foreach ($planning as $event) {
            // Collect Course entry
            if ($course_id != $event->course_id) {
                // New course, if it is not the first one, add the previous entry in the array
                if ($course_id != 0) {
                    $courses[$course_id] = array(
                        "schedule"  => $schedules,
                        "course_name" => $course_name,
                        "hours"     => $course_hours,
                        "gain"      => $course_gain,
                        "billable_rate" => $event->billable_rate,
                        "duration"  => $event->session_length
                    );
                }
                // New course start
                $course_name = $event->course_name;
                $course_id  = $event->course_id;
                $schedules = array();
                $course_hours  = 0;
                $course_gain   = 0;
            }

            if ($current_school != $event->school_name) {
                // New school, if it is not the first one, add the previous entry in the array
                if ($current_school != "") {
                    $schools[$current_school] = array(
                        "courses" => $courses,
                        "school_id" => $school_id,
                        "hours"   => $school_hours,
                        "gain"    => $school_gain
                    );
                }
                // New school start
                $current_school = $event->school_name;
                $school_id = $event->school_id;
                $courses = array();
                $school_hours  = 0;
                $school_gain   = 0;
            }
            // Collect Schedule entry
            $duration = self::sessionDurationHours($event->begin, $event->end);
            $gain     = self::planningGain($event->begin, $event->end, $event->rate, $event->billable_rate);

            $course_hours  += $duration;
            $course_gain   += $gain;

            $school_hours  += $duration;
            $school_gain   += $gain;

            $monthly_hours += $duration;
            $monthly_gain  += $gain;

            // Add the schedule entry to the array
            $schedules[$event->planning_id] = array(
                "group"    => $event->group_name,
                "begin"    => $event->begin,
                "end"      => $event->end,
                "duration" => $duration,
                "billable_rate" => $event->billable_rate,
                "bill"     => $event->invoice_id
            );
        }

        $courses[$course_id] = array(
            "schedule" => $schedules,
            "course_name" => $course_name,
            "hours"  => $course_hours,
            "gain"   => $course_gain,
            "duration" => $event->session_length
        );

        $schools[$current_school] = array(
            "courses" => $courses,
            "school_id" => $school_id,
            "hours"   => $school_hours,
            "gain"    => $school_gain
        );

        return [$schools, $monthly_gain, $monthly_hours];
    }

    public static function getInvoiceDetails($school_id, $month, $year, $invoice_id, $store = false)
    {
        $start_date =  trim($year) . "-" . substr("0" . trim($month), -2) . "-0 00:00:00";
        $month++;
        $end_year = $year;

        if ($month == "13") {
            $month = "01";
            $end_year++;
        }

        $end_date   =  trim($end_year) . "-" . substr("0" . trim($month), -2) . "-0 00:00:00";

        $planning_list = Planning::getPlanningBySchoolAndDate($school_id, $start_date, $end_date);

        $course_name = "";
        $group_name = "";
        $course_hours = 0;
        $course_gain  = 0;
        $total_amount = 0;
        $items = [];
        $items_total = [];
        $first_course = true;
        $rate = 0;
        foreach ($planning_list as $planning_detail) {
            if ($course_name != $planning_detail['course_name']) {
                // When a new course is detected we write previous course details and initialise counters
                if (!$first_course) {
                    // Skip first course to wait for planning data collection
                    array_unshift($items, [$course_name, "20%", $rate, $course_hours, "", "T"]);
                    $items_total = array_merge($items_total, $items);
                    $items = [];
                    $total_amount += $course_gain;
                    $course_hours = 0;
                    $course_gain  = 0;
                }
                $first_course = false;
                $course_name = $planning_detail['course_name'];
                $rate = $planning_detail['rate'];
                $group_name = "";
            }
            if ($group_name != $planning_detail['group_name']) {
                $group_name = $planning_detail['group_name'];
                array_push($items, [__('messages.invoice_line_group', ['name' => $group_name]), "", "", "", "", "S"]);
            }
            // Get planning details
            $planning = Planning::find($planning_detail['id']);
            $duration = self::sessionDurationHours($planning->begin, $planning->end);
            $billable_rate = self::billableMultiplier((float) $planning->billable_rate, (float) $rate);
            $duration = $duration * $billable_rate;
            $gain     = $duration * $rate;
            $course_hours  += $duration;
            $course_gain   += $gain;
            array_push($items, [" - " . date('d/m/Y H:i', strtotime($planning->begin)) . " - " . date('H:i', strtotime($planning->end)), "", "", $duration, $billable_rate, "N"]);
            if ($store) {
                $planning->invoice_id = $invoice_id;
                $planning->update();
            }
        }

        // Loop ended, we write current course details at top of current items list
        if (count($planning_list) > 0) {
            array_unshift($items, [$course_name, "20%", $rate, $course_hours, "", "T"]);
            $items_total = array_merge($items_total, $items);
            $total_amount += $course_gain;
        }

        $total_amount = round($total_amount, 2);

        return [$items_total, $total_amount];
    }
}
