<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\Course;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $programs = Program::all()->sortBy('name');
        return view('program.index', compact('programs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('program.create');
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
            Program::create([
                    'name' => $request->name,
                        ]);
            return redirect(route('program.index'))
                ->with([
                    'success' => "Programme enregistré avec succès"]);
        }
        catch (\Exception $e) {
            dd($e);
            return redirect()->back()
            ->with('error', "Erreur lors de l'enregitrement du programme");
        }          
    }

    /**
     * Display the specified resource.
     */
    public function show(String $program_id)
    {
        $program = Program::find($program_id);
        $courses = Course::where('program_id', '=', $program_id)->get();

        return view('program.show', compact('program', 'courses'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(String $program_id)
    {
        $program = Program::find($program_id);

        return view('program.edit', compact('program'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, String $program_id)
    {
        $validated = $request->validate([
            'name' => 'required|max:80',
        ]);
        
        try{
            $program = Program::findOrFail($program_id);
            $program->name = $request->name;
            $program->update();
                        
            return redirect(route('program.index'))
                ->with([
                    'success' => "Programme enregistré avec succès"]);
        }
        catch (\Exception $e) {
            dd($e);
            return redirect()->back()
            ->with('error', "Erreur lors de l'enregistrement du programme");
        }               
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(String $program_id)
    {
        $program = Program::findOrFail($program_id);
        $program->delete();
        return redirect(route('program.index'));
    }
}
