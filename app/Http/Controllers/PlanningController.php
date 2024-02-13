<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Group;
use App\Models\Planning;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PlanningController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
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

        if(isset($request->current_semester)){
            $current_semester = $request->current_semester;
            session(['current_semester' => $current_semester]);
        }else{
            $current_semester = session('current_semester');
            if (!isset($current_semester)) {
                 $current_semester = "all";
            }
        }

        // Collect Courses for future planning
        $schools = Auth::user()->getSchools();
        $years = $schools->getYears();
        $courses = $schools->getCourses($current_year, $current_semester);

        // Collect Planning information for display
        $planning = $schools->getPlanning($current_year, $current_month);
        $monthly_gain = 0;
        $monthly_hours = 0;
        foreach($planning as $event){
            $monthly_hours += $event->session_length;
            $monthly_gain += $event->session_length * $event->rate;
        }     
        //generate all the month names according to the current locale
        $months = [];
        for ($m=1; $m<=12; $m++) {
            $months[] = ucfirst(Carbon::parse(mktime(0,0,0,$m, 1, date('Y')))->translatedFormat('F'));
        }    
        //generate all the day names according to the current locale
        $weekdays = collect(Carbon::getDays())->map(fn($dayName) => ucfirst(Carbon::create($dayName)->dayName));
        // Trick to have weeks starting at Monday
        $weekdays->push($weekdays[0]);
        $weekdays->shift();

        return view('planning.index', compact('planning', 'courses', 'years', 'months', 'weekdays','current_year', 'current_month', 'monthly_gain', 'monthly_hours'));
    }

    /**
     * 
     */
    public function billing(Request $request)
    {
        $current_month = $request->month;
        $current_year = $request->year;
        $current_semester = "all";

        $schools = Auth::user()->schools()->get();

        // Collect Planning information for billing
        $planning = $schools->getBillingPlanning($current_year, $current_month);

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
                    $courses[$current_course] = array($schedules, $course_hours, $course_gain, $event->session_length);
                }
                $current_course = $event->course_name;
                $schedules = array();
                $course_hours  = 0;
                $course_gain   = 0;        
            }

            if($current_school != $event->school_name){
                if($current_school != ""){
                    $schools[$current_school] = array($courses, $school_hours, $school_gain);
                }
                $current_school = $event->school_name;
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

            $schedules[$event->planning_id] = array( "begin" => $event->begin, "end" => $event->end, "duration" => $duration);
        }
        $courses[$current_course] = array($schedules, $course_hours, $course_gain, $event->session_length);
        $schools[$current_school] = array($courses, $school_hours, $school_gain);

        $bills = Auth::user()->getBills();

        return view('planning.billing',compact('schools', 'current_year', 'current_month', 'monthly_gain', 'monthly_hours', 'bills'));
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $day = $request->day;
        $month = $request->month;
        $year = $request->year;

        $date = "$year-$month-$day";
        $course = Course::find($request->course);

        //TODO check groups are not yet fully booked
        $groups = $course->getGroups();
        
        $session_length = $course->session_length;

        return view('planning.create', compact('date', 'groups', 'session_length', 'course'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $group_id = $request->group;

        $session_length = $request->session_length;

        $date = $request->date;
        $hour = $request->hour;
        $minutes = $request->minutes;

        $begin = date('Y-m-d H:i:s',strtotime("$date $hour:$minutes:0"));
        //TODO session length is bugged
        $add_hours = intval($session_length);
        $add_minutes = ($session_length - $add_hours)*60;
        $end = date('Y-m-d H:i:s',strtotime("$date $hour:$minutes:0 +$add_hours hours +$add_minutes minutes"));

        try{
            Planning::create([
                    'begin' => $begin,
                    'end' => $end,
                    'location' => 'na',
                    'group_id' => $group_id
                        ]);
            return redirect(route('planning.index'))
                ->with([
                    'success' => "Session de cours enregistrée avec succès le ".$begin]);
        }
        catch (\Exception $e) {
            dd($e);
            return redirect()->back()
            ->with('error', "Erreur lors de l'enregitrement d'une session de cours");
        }     
    }

    /**
     * Display the specified resource.
     */
    public function show(Planning $planning)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(String $id)
    {
        $planning = Planning::findOrFail($id);

        $group = Group::findOrFail($planning->group_id);
        $course = Course::findOrFail($group->course_id);
        //TODO check groups are not yet fully booked
        $groups = $course->getGroups();

        return view('planning.edit', compact('planning', 'groups'));    
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Int $id)
    {
        try{
            $planning = Planning::findOrFail($id);
            $session_length = $planning->GetSessionLength();

            $day = $request->day;
            $month = $request->month;
            $year = $request->year;
    
            $date = "$year-$month-$day";

            $hour = $request->hour;
            $minutes = $request->minutes;

            $end_hour = $request->end_hour;
            $end_minutes = $request->end_minutes;

            $begin = date('Y-m-d H:i:s',strtotime("$date $hour:$minutes:0"));
            //TODO session length is bugged
            $end = date('Y-m-d H:i:s',strtotime("$date $end_hour:$end_minutes:0"));
    
            $planning->begin = $begin;
            $planning->end = $end;
            $planning->group_id = $request->group_id;

            $planning->save();

            return redirect(route('planning.index'))
                ->with([
                    'success' => "Session modifiée avec succès"]);
        }
        catch (\Exception $e) {
            dd($e);
            return redirect()->back()
            ->with('error', "Erreur lors de la modification de la session<br>".$e->message);
        }               


        return redirect(route('planning.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $planning = Planning::findOrFail($id);
        $planning->delete();
        
        return redirect(route('planning.index'));    
    }
}
