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

        return $current_month;
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

    public static function getBillingInformation($planning)
    {
        $current_school = "";
        $course_name = "";
        $course_id = 0;
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
            $end      = strtotime($event->end);
            $begin    = strtotime($event->begin);
            $duration = intval(($end - $begin) / 60) / 60;
            $gain     = $duration * $event->rate;

            $course_hours  += $duration;
            $course_gain   += $gain;

            $school_hours  += intval(($end - $begin) / 3600);
            $school_gain   += $gain;

            $monthly_hours += intval(($end - $begin) / 3600);
            $monthly_gain  += $gain;

            // Add the schedule entry to the array
            $schedules[$event->planning_id] = array(
                "group"    => $event->group_name,
                "begin"    => $event->begin,
                "end"      => $event->end,
                "duration" => $duration,
                "bill"     => $event->bill_id
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

    public static function getInvoiceDetails($school_id, $month, $year, $invoice_id)
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
                    array_unshift($items, [$course_name, "20%", $rate, $course_hours, "T"]);
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
                array_push($items, ["Groupe : " . $group_name, "", "", "", "S"]);
            }
            // Get planning details
            $planning = Planning::find($planning_detail['id']);
            $end      = strtotime($planning->end);
            $begin    = strtotime($planning->begin);
            $duration = intval(($end - $begin) / 60) / 60;
            $gain     = $duration * $rate;
            $course_hours  += $duration;
            $course_gain   += $gain;
            array_push($items, [" - " . date('d/m/Y H:i', strtotime($planning->begin)) . " - " . date('H:i', strtotime($planning->end)), "", "", $duration, "N"]);
            $planning->bill_id = $invoice_id;
            $planning->update();

        }
        //        dd($items);
        // Loop ended, we write current course details at top of current items list
        array_unshift($items, [$course_name, "20%", $rate, $course_hours, "T"]);
        $items_total = array_merge($items_total, $items);
        $total_amount += $course_gain;

        $total_amount = round($total_amount, 2);

        return [$items_total, $total_amount];
    }
}
