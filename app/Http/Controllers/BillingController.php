<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\Course;
use App\Models\Group;
use App\Models\GroupCourse;
use App\Models\Planning;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BillingController extends Controller
{
    /**
     * 
     */
    public function billing(Request $request)
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
        $current_year = (int) $current_year;

        if(isset($request->current_month)){
            $current_month = $request->current_month+1;
            session(['current_month' => $current_month]);
        }else {
            $current_month = session('current_month');
            if (!isset($current_month)) {
                $current_month = now()->format('m');
            }
        }

        return $this->buildBilling($current_month, $current_year);
    }

    public function previous(Request $request){
        if(isset($request->current_year)){
            $current_year = $request->current_year;
            session(['current_year' => $current_year]);
        }else {
            $current_year = session('current_year');
            if (!isset($current_year)) {
                $current_year = now()->format('Y');
            }
        }
        $current_year = (int) $current_year;

        if(isset($request->current_month)){
            $current_month = $request->current_month;
            if($current_month < 1){
                $current_month += 12;
                $current_year -= 1;
                session(['current_year' => $current_year]);
            }
            session(['current_month' => $current_month]);
        }else {
            $current_month = session('current_month');
            if (!isset($current_month)) {
                $current_month = now()->format('m');
            }
        }

        return $this->buildBilling($current_month, $current_year);
    }

    public function next(Request $request){
        if(isset($request->current_year)){
            $current_year = $request->current_year;
            session(['current_year' => $current_year]);
        }else {
            $current_year = session('current_year');
            if (!isset($current_year)) {
                $current_year = now()->format('Y');
            }
        }
        $current_year = (int) $current_year;

        if(isset($request->current_month)){
            $current_month = $request->current_month+2;
            if ($current_month > 11){
                $current_month -= 12;
                $current_year += 1;
                session(['current_year' => $current_year]);
            }
            session(['current_month' => $current_month]);
        }else {
            $current_month = session('current_month');
            if (!isset($current_month)) {
                $current_month = now()->format('m');
            }
        }

        return $this->buildBilling($current_month, $current_year);
    }

    private function buildBilling($current_month, $current_year){
        $current_semester = "all";
        $schools = Auth::user()->getSchools();
        $years = Auth::user()->getSchools()->getYears();
        //generate all the month names according to the current locale
        $months = [];
        for ($m=1; $m<=12; $m++) {
            $months[] = ucfirst(Carbon::parse(mktime(0,0,0,$m, 1, date('Y')))->translatedFormat('F'));
        }

        // Collect Planning information for billing
        $planning = $schools->getBillingPlanning($current_year, $current_month);

        if(!$planning)
        {
            $monthly_hours = 0;

            session()->flash('danger', "Pas de cours enregistré ce mois-ci.");

            return view('planning.billing',compact('schools', 'current_year', 'current_month', 'monthly_hours','years', 'months'));
            //            return redirect()->back()
        }

        $current_school = "";
        $current_course = "";
        $current_group = "";
        $monthly_hours = 0;
        $monthly_gain  = 0;
        $schools = array();
        $schedules = array();

        foreach($planning as $event){

            if($current_course != $event->course_name){
                if($current_course != ""){
                    $courses[$current_course] = array(
                        "schedule" => $schedules,
                        "course_id" => $course_id,
                        "hours"  => $course_hours,
                        "gain"   => $course_gain,
                        "duration" => $event->session_length);
                }
                $current_course = $event->course_name;
                $course_id  = $event->course_id;
                $schedules = array();
                $course_hours  = 0;
                $course_gain   = 0;        
            }

            if($current_school != $event->school_name){
                if($current_school != ""){
                    $schools[$current_school] = array(
                        "courses" => $courses,
                        "school_id" => $school_id,
                        "hours"   => $school_hours,
                        "gain"    => $school_gain);
                    $school_id = $event->school_id;
                }
                $current_school = $event->school_name;
                $school_id = $event->school_id;
                $courses = array();
                $school_hours  = 0;
                $school_gain   = 0;
            }

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

            $schedules[$event->planning_id] = array( 
                "group"    => $event->group_name,
                "begin"    => $event->begin,
                "end"      => $event->end,
                "duration" => $duration,
                "bill"     => $event->bill_id);
        }
        $courses[$current_course] = array(
        "schedule" => $schedules,
        "course_id" => $course_id,
        "hours"  => $course_hours,
        "gain"   => $course_gain,
        "duration" => $event->session_length);

        $schools[$current_school] = array(
            "courses" => $courses,
            "school_id" => $school_id,
            "hours"   => $school_hours,
            "gain"    => $school_gain);

        $bills = Auth::user()->getBills();    
        
        return view('planning.billing',compact('schools', 'current_year', 'current_month', 'monthly_gain', 'monthly_hours', 'bills', 'years', 'months'));    
    }

}
