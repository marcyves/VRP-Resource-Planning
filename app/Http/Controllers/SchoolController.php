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
    public function index(Request $request)
    {
        $schools = Auth::user()->schools()->get();

        return view('school.index', compact('schools'));

    }
    /**
     * Display a listing of the resource.
     */
    public function dashboard(Request $request)
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

        if(isset($request->current_semester)){
            $current_semester = $request->current_semester;
            session(['current_semester' => $current_semester]);
        }else{
            $current_semester = session('current_semester');
            if (!isset($current_semester)) {
                 $current_semester = "all";
            }
        }

        $schools = Auth::user()->schools()->get();
//        $courses = $schools->getCourses($current_year, $current_semester);
        //TODO use $list instead of $courses
        // $list = $schools->listCourses();
        $courses = Auth::user()->getCourses($current_year, $current_semester);
        $years = $schools->getYears();

        return view('dashboard', compact('courses', 'current_year', 'current_semester','years'));
    }

    public function list()
    {
        $schools = Auth::user()->schools()->get();
        $schools = $schools->getNoCourse();

        return view('school.list', compact('schools'));
    }

    public function add(String $school_id)
    {
        $school = School::find($school_id);
        return view('school.add', compact('school'));
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
            return redirect(route('school.list'))
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
    public function show(String $school_id)
    {
//        $school = School::findOrFail($school_id);
//        $courses = $school->courses()->get();
        $school = Auth::user()->schools()->where('id', '=', $school_id)->get();
        $courses = $school->getCourses();

        $school_name = $school[0]->name;
        $school_id = $school[0]->id;
        $documents = $school[0]->getDocuments();

        return view('school.show', compact('school_id', 'school_name', 'courses', 'documents'));
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
    public function destroy(String $school_id)
    {
        $school = School::findOrFail($school_id);
        $school->delete();
        
        return redirect(route('dashboard'));
    }

}
