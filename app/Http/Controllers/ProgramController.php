<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProgramController extends Controller
{
    private function companyProgram(string $programId): Program
    {
        return Program::forCompany(Auth::user()->company_id)->findOrFail($programId);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $programs = Program::forCurrentCompany()->orderBy('name')->get();

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
            'short_description' => 'nullable|string|max:80',
        ]);

        try {
            Program::create([
                'name' => $request->name,
                'short_description' => $request->short_description,
                'company_id' => Auth::user()->company_id,
            ]);

            session()->flash('success', __('messages.program_saved_success'));

            return redirect(route('program.index'));
        } catch (\Exception $e) {
            session()->flash('danger', __('messages.program_save_error'));

            return redirect()->back();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(String $program_id)
    {
        $program = $this->companyProgram($program_id);
        $courses = Course::getProgramCoursesForCompany($program_id)
            ->load('school')
            ->sortBy([
                ['year', 'desc'],
                ['semester', 'asc'],
                ['name', 'asc'],
            ]);

        return view('program.show', compact('program', 'courses'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(String $program_id)
    {
        $program = $this->companyProgram($program_id);

        return view('program.edit', compact('program'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, String $program_id)
    {
        $validated = $request->validate([
            'name' => 'required|max:80',
            'short_description' => 'nullable|string|max:80',
        ]);

        try {
            $program = $this->companyProgram($program_id);
            $program->name = $request->name;
            $program->short_description = $request->short_description;
            $program->update();

            session()->flash('success', __('messages.program_updated_success'));

            return redirect(route('program.index'));
        } catch (\Exception $e) {
            session()->flash('danger', __('messages.program_save_error'));

            return redirect()->back();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(String $program_id)
    {
        $program = $this->companyProgram($program_id);
        $program->delete();

        return redirect(route('program.index'));
    }
}
