<?php

namespace App\Http\Controllers;

use App\Models\Course;
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
        return view('course.create', compact('school_id'));
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
                    'school_id' => $school_id,
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
    public function show(Course $course)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Course $course)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Course $course)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Course $course)
    {
        //
    }
}
