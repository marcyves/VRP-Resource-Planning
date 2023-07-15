<?php

namespace App\Http\Controllers;

use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SchoolController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $schools = Auth::user()->getSchools();
        $courses = $schools->getCourses('2023');

        return view('dashboard', compact('schools', 'courses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('school.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:80',
        ]);
        
        try{
            $user_id = Auth::user()->id;
            School::create([
                    'name' => $request->name,
                    'user_id' => $user_id
                ]);
            return redirect(route('dashboard'))
                ->with([
                    'success' => "Ecole enregistré avec succès"]);
        }
        catch (\Exception $e) {
            dd($e);
            return redirect()->back()
            ->with('error', "Erreur lors de l'enregitrement de l'école");
        }               
    }

    /**
     * Display the specified resource.
     */
    public function show(School $school)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(String $school_id)
    {
        $school = School::findOrFail($school_id);
        return view('school.edit', compact('school'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, String $school_id)
    {
        $validated = $request->validate([
            'name' => 'required|max:80',
        ]);
        
        try{
            $school = School::findOrFail($school_id);
            $school->name = $request->name;
            $school->save();

            return redirect(route('dashboard'))
                ->with([
                    'success' => "Ecole enregistrée avec succès"]);
        }
        catch (\Exception $e) {
            dd($e);
            return redirect()->back()
            ->with('error', "Erreur lors de l'enregitrement de l'école");
        }               

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(School $school)
    {
        //
    }
}
