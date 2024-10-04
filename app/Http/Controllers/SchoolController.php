<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\Bill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SchoolController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        session()->forget('course');
        session()->forget('course_id');
        session()->forget('school');
        session()->forget('school_id');

        $schools = Auth::user()->getSchools();

        return view('school.index', compact('schools'));

    }
    /**
     * Display a listing of the resource.
     */
    public function dashboard(Request $request)
    {
        session()->forget('course');
        session()->forget('course_id');
        session()->forget('school');
        session()->forget('school_id');
        
        if(isset($request->current_year)){
            $current_year = $request->current_year;
        }else {
            $current_year = session('current_year');
            if (!isset($current_year)) {
                $current_year = now()->format('Y');
            }
        }
        session()->put('current_year', $current_year);

        if(isset($request->current_semester)){
            $current_semester = $request->current_semester;
        }else{
            $current_semester = session('current_semester');
            if (!isset($current_semester)) {
                 $current_semester = "all";
            }
        }
        session()->put('current_semester', $current_semester);

        $schools = Auth::user()->getSchools();
        //TODO use $list instead of $courses
        // $list = $schools->listCourses();
        $courses = Auth::user()->getCourses($current_year, $current_semester);
        $years = $schools->getYears();
        session()->put('years', $years);

        $bills_amount = Bill::getAmount($current_year);
        $bills_payed_amount = Bill::getPayedAmount($current_year);
        $bills_count = Bill::getCount($current_year);

        return view('dashboard', compact('courses', 'current_year', 'current_semester','years', 'bills_amount', 'bills_payed_amount', 'bills_count'));
    }

    public function list()
    {
        $schools = Auth::user()->getSchools();
        $schools = $schools->getNoCourse();

        return view('school.list', compact('schools'));
    }

    public function add(String $school_id)
    {
        $school = School::find($school_id);

        session()->put('school', $school->name);
        session()->put('school_id', $school->id);

        return view('school.add', compact('school'));
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
            $company_id = Auth::user()->company_id;
            $school = School::create([ 
                    'name' => $request->name,
                    'company_id' => $company_id
                ]);

            session()->flash('success', 'Ecole '.$school->name.' enregistrée avec succès.');
            session()->put('school', $school->name);
            session()->put('school_id', $school->id);

            return redirect(route('school.list'));
        }
        catch (\Exception $e) {
            // dd($e);
            
            session()->flash('danger', "Erreur lors de l'enregistrement de l'école ".$request->name.'.');
            
            return redirect()->back();
        }               
    }

    /**
     * Display the specified resource.
     */
    public function show(School $school)
    {

        $year = session()->get('current_year');
        if (!isset($year)) {
            $year = now()->format('Y');
        }

        $courses = $school->getCourses($year);

        session()->forget('course');
        session()->forget('course_id');

        session()->put('school', $school->name);
        session()->put('school_id', $school->id);

        $school_name = $school->name;
        $school_id = $school->id;
        $documents = $school->getDocuments();

        return view('school.show', compact('school_id', 'school_name', 'courses', 'documents'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(String $school_id)
    {
        $school = School::findOrFail($school_id);
        session()->put('school', $school->name);
        session()->put('school_id', $school->id);

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
            session()->put('school_id', $school_id);

            session()->put('school', $school->name);

            $school->save();

            session()->flash('success', 'Ecole '.$request->name.' modifiée avec succès.');

            return redirect(route('dashboard'));
        }
        catch (\Exception $e) {
            // dd($e);

            session()->flash('danger', "Erreur lors de la modification de l'école ".$request->name.'.');

            return redirect()->back();
        }               

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(School $school)
    {
        if ($school->countCourses() > 0){

            session()->flash('danger', "On ne peut pas effacer une école qui a des cours enregistrés.");
            return redirect()->back();
        }
        session()->forget('school');
        session()->forget('school_id');

        $school->delete();
        
        session()->flash('warning', "Ecole supprimée avec succès.");
        
        return redirect()->back();
    }

}
