<?php

namespace App\Http\Controllers;

use App\Http\Utility\Tools;
use App\Models\Group;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

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
        session()->put('last_school_id', $school->id);

        return view('school.add', compact('school'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->normalizeSchoolCode($request);

        $validated = $request->validate(
            array_merge([
                'name' => 'required|max:80',
            ], $this->schoolCodeRules()),
            $this->schoolCodeMessages()
        );

        try {
            $company_id = Auth::user()->company_id;
            $school = School::create([
                'name' => $validated['name'],
                'code' => $validated['code'] ?? null,
                'company_id' => $company_id,
                'siren' => $request->siren,
                'siret' => $request->siret,
                'vat_number' => $request->vat_number,
                'electronic_address' => $request->electronic_address,
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

            session()->flash('success', __('messages.school_saved_success', ['name' => $school->name]));
            session()->put('school', $school->name);
            session()->put('school_id', $school->id);
            session()->put('last_school_id', $school->id);

            return redirect(route('school.index'));
        } catch (\Exception $e) {
            dd($e);

            session()->flash('danger', __('messages.school_save_error', ['name' => $request->name]));

            return redirect()->back();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(School $school, Request $request)
    {
        $year = Tools::getCurrentYear($request);
        $billingYear = Tools::getBillingYear($request);
        $currentMonth = Tools::getCurrentMonth($request);
        $months = Tools::getMonthNames();
        $years = Auth::user()->getSchools()->getYears();
        $billingByDate = session('school_billing_by_date', false);

        $courses = $school->getCourses($year);

        session()->forget('course');
        session()->forget('course_id');

        session()->put('school', $school->name);
        session()->put('school_id', $school->id);
        session()->put('last_school_id', $school->id);

        view()->share('schoolContextSchool', $school);

        $panel = $request->query('panel', 'courses');
        if ($request->query('focus') === 'billing') {
            $panel = 'courses';
        }
        if ($panel === 'address') {
            $panel = 'details';
        }
        if (! in_array($panel, ['courses', 'groups', 'details', 'documents'], true)) {
            $panel = 'courses';
        }

        $invoices = collect();
        $documents = collect();
        $bills = collect();
        $groups = collect();
        $inactiveGroups = collect();
        $occurences = collect();
        $billingData = null;
        $monthlyHours = 0;
        $monthlyGain = 0;
        $hasPreviousUnbilled = false;

        if ($panel === 'courses') {
            $invoices = $school->getInvoices($year);
            $bills = $invoices;

            $planning = $school->getBillingPlanning($billingYear, $currentMonth);
            if ($planning) {
                [$schoolsBilling, $monthlyGain, $monthlyHours] = Tools::getBillingInformation($planning);
                $billingData = reset($schoolsBilling) ?: null;
            }

            $hasPreviousUnbilled = $school->hasPreviousUnbilledPeriod($billingYear, $currentMonth);
        } elseif ($panel === 'groups') {
            $groups = $school->getLinkedGroups(true);
            $inactiveGroups = $school->getLinkedGroups(false);
            $occurences = Group::planningOccurrencesForIds(
                $groups->pluck('id')->merge($inactiveGroups->pluck('id')),
                'all'
            );
        } elseif ($panel === 'documents') {
            $documents = $school->getDocuments();
        }

        return view('school.show', compact(
            'school',
            'courses',
            'documents',
            'invoices',
            'billingData',
            'monthlyHours',
            'monthlyGain',
            'year',
            'billingYear',
            'currentMonth',
            'months',
            'years',
            'bills',
            'billingByDate',
            'hasPreviousUnbilled',
            'panel',
            'groups',
            'inactiveGroups',
            'occurences',
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $school_id)
    {
        $school = School::findOrFail($school_id);
        session()->put('school', $school->name);
        session()->put('school_id', $school->id);
        session()->put('last_school_id', $school->id);

        view()->share('schoolContextSchool', $school);

        return view('school.edit', compact('school'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $school_id)
    {
        $this->normalizeSchoolCode($request);

        $validated = $request->validate(
            array_merge([
                'name' => 'required|max:80',
                'context' => ['nullable', 'in:'.implode(',', \App\Support\SchoolContext::values())],
                'siren' => ['nullable', 'digits:9'],
                'siret' => ['nullable', 'digits:14'],
                'vat_number' => ['nullable', 'string', 'max:20'],
                'electronic_address' => ['nullable', 'string', 'max:100'],
            ], $this->schoolCodeRules((int) $school_id)),
            $this->schoolCodeMessages()
        );

        try {
            $school = School::findOrFail($school_id);
            $school->name = $validated['name'];
            $school->code = $validated['code'] ?? null;
            $school->context = $request->input('context', \App\Support\SchoolContext::EDUCATION);
            $school->siren = $request->siren;
            $school->siret = $request->siret;
            $school->vat_number = $request->vat_number;
            $school->electronic_address = $request->electronic_address;
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
            session()->put('last_school_id', $school_id);

            session()->put('school', $school->name);

            $school->save();

            session()->flash('success', __('messages.school_updated_success', ['name' => $validated['name']]));

            return redirect()->to(route('school.show', $school).'?panel=details');
        } catch (\Exception $e) {
            // dd($e);

            session()->flash('danger', __('messages.school_update_error', ['name' => $request->name]));

            return redirect()->back();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(School $school)
    {
        if ($school->countCourses() > 0) {

            session()->flash('danger', __('messages.school_delete_has_courses'));

            return redirect()->back();
        }
        session()->forget('school');
        session()->forget('school_id');

        $school->delete();

        session()->flash('warning', __('messages.school_deleted_success'));

        return redirect()->back();
    }

    private function normalizeSchoolCode(Request $request): void
    {
        $code = $request->input('code');

        $request->merge([
            'code' => is_string($code) && trim($code) !== '' ? trim($code) : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function schoolCodeRules(?int $ignoreSchoolId = null): array
    {
        return [
            'code' => ['nullable', 'string', 'max:80', $this->uniqueSchoolCodeRule($ignoreSchoolId)],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function schoolCodeMessages(): array
    {
        return [
            'code.unique' => __('messages.school_code_taken'),
        ];
    }

    private function uniqueSchoolCodeRule(?int $ignoreSchoolId = null): Unique
    {
        $rule = Rule::unique('schools', 'code')->where(
            fn ($query) => $query->where('company_id', Auth::user()->company_id)
        );

        if ($ignoreSchoolId !== null) {
            $rule->ignore($ignoreSchoolId);
        }

        return $rule;
    }
}
