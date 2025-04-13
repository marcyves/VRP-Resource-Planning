<?php
namespace App\Http\Utility;

use Illuminate\Http\Request;
use Carbon\Carbon;

class Tools
{
    public static function getCurrentDay(Request $request)
    {
        if(isset($request->current_day)){
            $current_day = $request->current_day+1;
            session(['current_day' => $current_day]);
        }else {
            $current_day = session('current_day');
            if (!isset($current_day)) {
                $current_day = now()->format('d');
            }
        }
        return $current_day;
    }
    
    public static function getCurrentYear(Request $request)
    {

        if(isset($request->current_year)){
            $current_year = $request->current_year;
            session(['current_year' => $current_year]);
        }else {
            $current_year = session('current_year');
            if (!isset($current_year)) {
                $current_year = now()->format('Y');
            }
        }

        return (int) $current_year;
    }

    public static function getCurrentMonth(Request $request)
    {
        if(isset($request->current_month)){
            $current_month = $request->current_month+1;
            session(['current_month' => $current_month]);
        }else {
            $current_month = session('current_month');
            if (!isset($current_month)) {
                $current_month = now()->format('m');
            }
        }
        
        return $current_month;
    }

    public static function getCurrentSemester(Request $request)
    {
        if(isset($request->current_semester)){
            $current_semester = $request->current_semester;
            session(['current_semester' => $current_semester]);
        }else{
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
        for ($m=1; $m<=12; $m++) {
            $months[] = ucfirst(Carbon::parse(mktime(0,0,0,$m, 1, date('Y')))->translatedFormat('F'));
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

        foreach($planning as $event){
            // Collect Course entry
            if($course_id != $event->course_id){
                // New course, if it is not the first one, add the previous entry in the array
                if($course_id != 0){
                    $courses[$course_id] = array(
                        "schedule"  => $schedules,
                        "course_name" => $course_name,
                        "hours"     => $course_hours,
                        "gain"      => $course_gain,
                        "duration"  => $event->session_length);
                }
                // New course start
                $course_name = $event->course_name;
                $course_id  = $event->course_id;
                $schedules = array();
                $course_hours  = 0;
                $course_gain   = 0;        
            }

            if($current_school != $event->school_name){
                // New school, if it is not the first one, add the previous entry in the array
                if($current_school != ""){
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
            $duration = intval(($end - $begin)/60)/60;
            $gain     = $duration * $event->rate;

            $course_hours  += $duration;
            $course_gain   += $gain;

            $school_hours  += intval(($end - $begin)/3600);
            $school_gain   += $gain;
            
            $monthly_hours += intval(($end - $begin)/3600);
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
            "duration" => $event->session_length);

        $schools[$current_school] = array(
            "courses" => $courses,
            "school_id" => $school_id,
            "hours"   => $school_hours,
            "gain"    => $school_gain);

        return [$schools, $monthly_gain, $monthly_hours];
    }
}
