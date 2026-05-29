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
        session()->forget('course');
        session()->forget('course_id');
        session()->forget('school');
        session()->forget('school_id');

        $current_year = session('current_year');
        if (! isset($current_year)) {
            $current_year = now()->format('Y');
            session()->put('current_year', $current_year);
        }

        $schools = Auth::user()->getSchoolsAndBudget($current_year);
        $inactiveSchools = Auth::user()->getSchools()->getNoCourse($current_year);

        return view('school.index', compact('schools', 'inactiveSchools', 'current_year'));
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

        if (isset($request->current_year)) {
            $current_year = $request->current_year;
        } else {
            $current_year = session('current_year');
            if (! isset($current_year)) {
                $current_year = now()->format('Y');
            }
        }
        session()->put('current_year', $current_year);

        if (isset($request->current_semester)) {
            $current_semester = $request->current_semester;
        } else {
            $current_semester = session('current_semester');
            if (! isset($current_semester)) {
                $current_semester = 'all';
            }
        }
        session()->put('current_semester', $current_semester);

        $schools = Auth::user()->getSchools($current_year);
        $courses = Auth::user()->getCourses($current_year, $current_semester);
        $years = $schools->getYears();
        session()->put('years', $years);

        return view('dashboard', compact('schools', 'courses', 'current_year', 'current_semester', 'years'));
    }

    public function list()
    {
        return redirect()->route('school.index', [], 301);
    }

    public function add(string $school_id)
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
        // dd($request->all());
        $validated = $request->validate([
            'name' => 'required|max:80',
        ]);

        try {
            $company_id = Auth::user()->company_id;
            $school = School::create([
                'name' => $request->name,
                'company_id' => $company_id,
                'siren' => $request->siren,
                'siret' => $request->siret,
                'vat_number' => $request->vat_number,
                'address' => $request->address,
                'address2' => $request->address2,
                'city' => $request->city,
                'zip' => $request->zip,
                'country' => $request->country,
                'phone' => $request->phone,
                'email' => $request->email,
                'website' => $request->website,
                'logo' => $request->logo,
                'description' => $request->description,
            ]);

            session()->flash('success', 'Ecole '.$school->name.' enregistrée avec succès.');
            session()->put('school', $school->name);
            session()->put('school_id', $school->id);

            return redirect(route('school.index'));
        } catch (\Exception $e) {
            dd($e);

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
        if (! isset($year)) {
            $year = now()->format('Y');
        }

        $courses = $school->getCourses($year);

        session()->forget('course');
        session()->forget('course_id');

        session()->put('school', $school->name);
        session()->put('school_id', $school->id);

        // $school_name = $school->name;
        // $school_id = $school->id;
        $invoices = $school->getInvoices($year);
        $documents = $school->getDocuments();

        return view('school.show', compact('school', 'courses', 'documents', 'invoices'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $school_id)
    {
        $school = School::findOrFail($school_id);
        session()->put('school', $school->name);
        session()->put('school_id', $school->id);

        return view('school.edit', compact('school'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $school_id)
    {
        $validated = $request->validate([
            'name' => 'required|max:80',
            'siren' => ['nullable', 'digits:9'],
            'siret' => ['nullable', 'digits:14'],
            'vat_number' => ['nullable', 'string', 'max:20'],
        ]);

        try {
            $school = School::findOrFail($school_id);
            $school->name = $request->name;
            $school->code = $request->code;
            $school->siren = $request->siren;
            $school->siret = $request->siret;
            $school->vat_number = $request->vat_number;
            $school->address = $request->address;
            $school->address2 = $request->address2;
            $school->city = $request->city;
            $school->zip = $request->zip;
            $school->country = $request->country;
            $school->phone = $request->phone;
            $school->email = $request->email;
            $school->website = $request->website;
            $school->logo = $request->logo;
            $school->description = $request->description;

            session()->put('school_id', $school_id);

            session()->put('school', $school->name);

            $school->save();

            session()->flash('success', 'Ecole '.$request->name.' modifiée avec succès.');

            return redirect(route('dashboard'));
        } catch (\Exception $e) {
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
        if ($school->countCourses() > 0) {

            session()->flash('danger', 'On ne peut pas effacer une école qui a des cours enregistrés.');

            return redirect()->back();
        }
        session()->forget('school');
        session()->forget('school_id');

        $school->delete();

        session()->flash('warning', 'Ecole supprimée avec succès.');

        return redirect()->back();
    }
}
