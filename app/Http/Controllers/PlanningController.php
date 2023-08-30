<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Planning;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $schools = Auth::user()->schools()->get();
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
        return view('planning.index', compact('planning', 'courses', 'years', 'current_year', 'current_month', 'monthly_gain', 'monthly_hours'));
    }

    public function insert(Request $request)
    {
        $day = $request->day;
        $month = $request->month;
        $year = $request->year;

        $date = "$year-$month-$day";
        $course = Course::find($request->course);

        //TODO check groups are not yet fully booked
        $groups = $course->getGroups();
        
        $session_length = $course->session_length;

        return view('planning.insert', compact('date', 'groups', 'session_length'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(String $group_id)
    {
        return view('planning.create', compact('group_id'));
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
        $end = date('Y-m-d H:i:s',strtotime("$date $hour:$minutes:0 +$session_length hours"));

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
        
        return view('planning.edit', compact('planning'));    
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Planning $planning)
    {
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
