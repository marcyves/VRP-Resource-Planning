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

class PlanningController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
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

        return $this->buildPlanning($current_semester, $current_month, $current_year);
    }

    public function previous(Request $request)
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

        return $this->buildPlanning($current_semester, $current_month, $current_year);
    }

    public function next(Request $request)
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

        return $this->buildPlanning($current_semester, $current_month, $current_year);
    }

    private function buildPlanning($current_semester, $current_month, $current_year){

        if( $school_id = session()->get('school_id')){
            $schools = School::find($school_id);
            $years  = Course::where('school_id', $school_id)
            ->select(['year'])
            ->distinct()
            ->orderBy('year', 'asc')
            ->get();
           
            if( $course_id = session()->get('course_id')){
                $courses = Course::select(['courses.*', 'programs.name as program_name'])
                    ->where('courses.id', '=', $course_id)
                    ->where('year', '=', $current_year)
                    ->leftJoin('programs', 'courses.program_id', '=', 'programs.id')
                    ->orderBy('semester', 'asc')
                    ->orderBy('program_name', 'asc')
                    ->orderBy('name', 'asc')
                    ->get();
                $mode = 'selected';
            }else{
                $courses = $schools->getCourses();
                $mode = 'single';
            }
        }else{
            // Collect Courses for future planning
            $schools = Auth::user()->getSchools();
            $years = $schools->getYears();
            $courses = $schools->getCourses($current_year, $current_semester);
            $mode = 'multi';
        }
        // Collect Planning information for display
        //$planning = $schools->getPlanning($current_year, $current_month);
        $planning = Planning::getDetails($current_year, $current_month);
        
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

        return view('planning.index', compact('planning', 'schools', 'courses', 'years', 'months', 'weekdays','current_year', 'current_month', 'monthly_gain', 'monthly_hours', 'mode'));
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

        session()->put('course', $course->name);
        session()->put('course_id', $course->id);

        $school = $course->getSchool();

        session()->put('school', $school->name);
        session()->put('school_id', $school->id);

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
        $course_id = $request->course;

        if($group_id == 0){
            $validated = $request->validate([
                'name' => 'required|max:80',
                'short_name' => 'required|min:3',
                'size' => 'required|min:0',
            ]);
            
            $company_id = Auth::user()->company_id;
            $active = false;
    
            if($course_id ==0 && session('course_id') != null){
                $course_id = session('course_id');
                $active = true;
            }
    
            try{
                $group = Group::create([
                        'name' => $request->name,
                        'short_name' => $request->short_name,
                        'size' => $request->size,
                        'course_id' => $course_id,
                        'company_id' => $company_id,
                        'active' => $active,
                    ]);

                $group_id = $group->id;
    
                session()->flash('success', "Groupe enregistré avec succès.");
    
                if ($course_id == 0){
                    session()->flash('danger', "Pas de cours sélectionné");
                    return redirect()->back();
                }else{
                    GroupCourse::create([
                        'group_id' => $group->id,
                        'course_id' => $course_id
                    ]);    
                }
            }
            catch (\Exception $e) {
                // dd($e);
                session()->flash('danger', "Erreur lors de l'enregistrement du groupe.");
    
                return redirect()->back();
            }         
        }

        $session_length = $request->session_length;

        $date = $request->date;
        $hour = $request->hour;
        $minutes = $request->minutes;

        $begin = date('Y-m-d H:i:s',strtotime("$date $hour:$minutes:0"));
        //TODO session length is bugged
        $add_hours = intval($session_length);
        $add_minutes = ($session_length - $add_hours)*60;
        $end = date('Y-m-d H:i:s',strtotime("$date $hour:$minutes:0 +$add_hours hours +$add_minutes minutes"));

        session()->remove('course');
        session()->remove('course_id');
        
        try{
            Planning::create([
                    'begin' => $begin,
                    'end' => $end,
                    'location' => 'na',
                    'group_id' => $group_id,
                    'course_id' => $course_id,
                        ]);

                session()->flash('success', "Session de cours enregistrée avec succès le ".$begin.".");

                return redirect(route('planning.index'));
        }
        catch (\Exception $e) {
            // dd($e);
            session()->flash('danger', "Erreur lors de l'enregitrement d'une session de cours.");

            return redirect()->back();
        }     
    }

    /**
     * Display the specified resource.
     */
    public function setBill(Request $request)
    {
        $school_id = $request->school_id;
        $course_id = $request->course_id;
        $month = $request->month;
        $year  = $request->year;

        $start_date =  trim($year)."-".substr("0".trim($month),-2)."-0 00:00:00";
        $month++;
        $end_year = $year;

        if($month == "13"){
            $month = "01";
            $end_year++;
        }
        
        $end_date   =  trim($end_year)."-".substr("0".trim($month),-2)."-0 00:00:00";

        $planning_list = Planning::getPlanningBySchoolAndDate($school_id, $start_date, $end_date);

        foreach($planning_list as $id)
        {
            $planning = Planning::find($id['id']);
            $planning->bill_id = $request->bill_id;
            $planning->update();
        }

        session()->flash('success', "Facture enregistrée avec succès.");

        return redirect(route('planning.index'));
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
        $current_group = Group::find($planning->group_id);
        $course = Course::findOrFail($planning->course_id);
        //TODO check groups are not yet fully booked
        $groups = Group::all();
        
        /*
        select()
        ->join('group_course', 'groups.id', '=', 'group_course.group_id')
        ->where('group_course.course_id', '=', $course->id)
        ->orderBy('nam', 'asc')
        ->get();
        */
        $courses = Auth::user()->getCourses();

        return view('planning.edit', compact('planning', 'current_group', 'groups', 'courses'));    
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
            $planning->course_id = $request->course_id;

            $planning->save();

            session()->flash('success', "Session modifiée avec succès.");

        }
        catch (\Exception $e) {
            session()->flash('danger', "Erreur lors de la modification de la session");
            //session()->flash('danger', $e->getMessage());
            return redirect()->back();
        }
        return redirect(route('planning.billing'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try{
            $planning = Planning::findOrFail($id);
            $planning->delete();
    
            session()->flash('success', "Session effacée avec succès.");
        }
        catch (\Exception $e) {
            session()->flash('danger', "Erreur lors de l'effacement de la session.");
            //session()->flash('danger', $e->getMessage());
            return redirect()->back();
        }

        return redirect(route('planning.index'));    
    }
}
