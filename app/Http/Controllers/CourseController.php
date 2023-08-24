<?php

namespace App\Http\Controllers;

use App\Models\Course;
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
        $programs = Program::all()->sortBy('name');

        return view('course.create', compact('school_id', 'programs'));
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
            Course::create([
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
            return redirect(route('dashboard'))
                ->with([
                    'success' => "Cours enregistré avec succès"]);
        }
        catch (\Exception $e) {
            dd($e);
            return redirect()->back()
            ->with('error', "Erreur lors de l'enregitrement du cours");
        }               
    }

    /**
     * Display the specified resource.
     */
    public function show(String $course_id)
    {
        $course = Course::getCourseDetails($course_id);
        $groups = $course->getGroups();

        return view('course.show', compact('course', 'groups'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(String $course_id)
    {
        $course = Course::getCourseDetails($course_id);
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
            'sessions' => 'required|min:0',
            'session_length' => 'required|min:0',
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
                        
            return redirect(route('dashboard'))
                ->with([
                    'success' => "Cours enregistré avec succès"]);
        }
        catch (\Exception $e) {
            dd($e);
            return redirect()->back()
            ->with('error', "Erreur lors de l'enregistrement du cours");
        }               
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(String $course_id)
    {
        $course = Course::findOrFail($course_id);
        $course->delete();
        return redirect(route('dashboard'));
    }
}
