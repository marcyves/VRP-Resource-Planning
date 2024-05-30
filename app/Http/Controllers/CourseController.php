<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\School;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(String $school_id)
    {
        $school = School::find($school_id);
        $programs = Program::all()->sortBy('name');

        return view('course.create', compact('school', 'programs'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, String $school_id)
    {
        $validated = $request->validate([
            'name' => 'required|max:80',
            'sessions' => 'required|min:0',
            'session_length' => 'required|min:0',
            'year' => 'required',
            'semester' => 'required',
            'rate' => 'required|min:0'
        ]);
        
        try{
            $user_id = Auth::user()->id;
            $course = Course::create([
                'name' => $request->name,
                'short_name' => $request->short_name,
                'school_id' => $school_id,
                    'program_id' => $request->program_id,
                    'sessions' => $request->sessions,
                    'session_length' => $request->session_length,
                    'year' => $request->year,
                    'semester' => $request->semester,
                    'rate' => $request->rate,
                        ]);

            session()->flash('success', "Cours ".$request->name." enregistré avec succès.");
            session()->put('course', $request->name);
            session()->put('course_id', $course->id);

            return redirect(route('dashboard'));
        }
        catch (\Exception $e) {
            dd($e);
            session()->flash('danger', "Erreur lors de l'enregitrement du cours.");

            return redirect()->back();
        }               
    }

    /**
     * Display the specified resource.
     */
    public function show(String $course_id)
    {
        $course = Course::getCourseDetails($course_id);
        $school = School::find($course->school_id);

        session()->put('course', $course->name);
        session()->put('course_id', $course->id);
        
        session()->put('school_id', $course->school_id);
        session()->put('school', $school->name);

        $groups = $course->getGroups();
        $available_groups = $course->getAvailableGroups();
        $occurences = $groups->getGroupOccurences();

        return view('course.show', compact('course', 'groups', 'available_groups', 'occurences'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(String $course_id)
    {
        $course = Course::getCourseDetails($course_id);
        session()->put('course', $course->name);
        session()->put('course_id', $course->id);

        $programs = Program::all()->sortBy('name');
        return view('course.edit', compact('course', 'programs'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, String $course_id)
    {
        $validated = $request->validate([
            'name' => 'required|max:80',
            'short_name' => 'required|min:3',
            'sessions' => 'required|numeric|min:0',
            'session_length' => 'required|numeric|min:0',
            'year' => 'required',
            'semester' => 'required',
            'rate' => 'required|min:0'
        ]);

        
        try{
            $course = Course::findOrFail($course_id);
            $course->name = $request->name;
            $course->short_name = $request->short_name;
            $course->sessions = $request->sessions;
            $course->session_length = $request->session_length;
            $course->year = $request->year;
            $course->semester = $request->semester;
            $course->rate = $request->rate;
            $course->program_id = $request->program_id;

            $course->update();

            session()->flash('success', "Cours ".$course->name." enregistré avec succès.");
            session()->put('course', $course->name);
            session()->put('course_id', $course->id);

            return redirect(route('dashboard'));
        }
        catch (\Exception $e) {
            dd($e);
            session()->flash('danger', "Erreur lors de l'enregitrement du cours.");

            return redirect()->back();
        }               
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(String $course_id)
    {
        $course = Course::findOrFail($course_id);
        session()->forget('course');
        session()->forget('course_id');
        $course->delete();
        return redirect(route('dashboard'));
    }
}
