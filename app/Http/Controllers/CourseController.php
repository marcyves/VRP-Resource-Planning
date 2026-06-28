<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\School;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CourseController extends Controller
{
    private function companySchool(string $schoolId): School
    {
        return School::query()
            ->where('company_id', Auth::user()->company_id)
            ->findOrFail($schoolId);
    }

    private function companyCourse(string $courseId): Course
    {
        return Course::query()
            ->whereHas('school', fn ($q) => $q->where('company_id', Auth::user()->company_id))
            ->findOrFail($courseId);
    }

    private function programRules(): array
    {
        return [
            'program_id' => [
                'required',
                Rule::exists('programs', 'id')->where(
                    fn ($query) => $query->where('company_id', Auth::user()->company_id)
                ),
            ],
        ];
    }

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
        $school = $this->companySchool($school_id);
        $programs = Program::forCurrentCompany()->orderBy('name')->get();

        return view('course.create', compact('school', 'programs'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, String $school_id)
    {
        $school = $this->companySchool($school_id);

        $validated = $request->validate(array_merge([
            'name' => 'required|max:80',
            'short_name' => 'required|min:3',
            'sessions' => 'required|min:0',
            'session_length' => 'required|min:0',
            'year' => 'required',
            'semester' => 'required',
            'rate' => 'required|min:0',
        ], $this->programRules()));

        try {
            $rate = str_replace(',', '.', $request->rate);
            $course = Course::create([
                'name' => $request->name,
                'short_name' => $request->short_name,
                'school_id' => $school->id,
                'program_id' => $request->program_id,
                'sessions' => $request->sessions,
                'session_length' => $request->session_length,
                'year' => $request->year,
                'semester' => $request->semester,
                'rate' => $rate,
            ]);

            session()->flash('success', __('messages.course_saved_success', ['name' => $request->name]));
            session()->put('course', $request->name);
            session()->put('course_id', $course->id);

            return redirect(route('dashboard'));
        } catch (\Exception $e) {
            session()->flash('danger', __('messages.course_save_error'));

            return redirect()->back();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(String $course_id)
    {
        $this->companyCourse($course_id);
        $course = Course::getCourseDetails($course_id);
        $school = School::find($course->school_id);

        session()->put('course', $course->name);
        session()->put('course_id', $course->id);

        session()->put('school_id', $course->school_id);
        session()->put('school', $school->name);

        $groups = $course->getLinkedGroups(true);
        $inactive_linked_groups = $course->getLinkedGroups(false);
        $available_groups = $course->getAvailableGroups();
        $allLinkedForPlanning = $groups->merge($inactive_linked_groups);
        $occurences = $allLinkedForPlanning->getGroupOccurences();

        return view('course.show', compact('course', 'groups', 'inactive_linked_groups', 'available_groups', 'occurences'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(String $course_id)
    {
        $this->companyCourse($course_id);
        $course = Course::getCourseDetails($course_id);
        session()->put('course', $course->name);
        session()->put('course_id', $course->id);

        $programs = Program::forCurrentCompany()->orderBy('name')->get();

        return view('course.edit', compact('course', 'programs'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, String $course_id)
    {
        $validated = $request->validate(array_merge([
            'name' => 'required|max:80',
            'short_name' => 'required|min:3',
            'sessions' => 'required|numeric|min:0',
            'session_length' => 'required|numeric|min:0',
            'year' => 'required',
            'semester' => 'required',
            'rate' => 'required|min:0',
        ], $this->programRules()));

        try {
            $course = $this->companyCourse($course_id);
            $course->name = $request->name;
            $course->short_name = $request->short_name;
            $course->sessions = $request->sessions;
            $course->session_length = $request->session_length;
            $course->year = $request->year;
            $course->semester = $request->semester;
            $course->rate = $request->rate;
            $course->program_id = $request->program_id;

            $course->update();

            session()->flash('success', __('messages.course_updated_success', ['name' => $course->name]));
            session()->put('course', $course->name);
            session()->put('course_id', $course->id);

            return redirect(route('dashboard'));
        } catch (\Exception $e) {
            session()->flash('danger', __('messages.course_save_error'));
            session()->flash('danger', $e->getMessage());

            return redirect()->back();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(String $course_id)
    {
        try {
            $course = $this->companyCourse($course_id);
            session()->forget('course');
            session()->forget('course_id');
            $course->delete();

            return redirect(route('dashboard'));
        } catch (\Exception $e) {
            session()->flash('danger', __('messages.course_delete_error'));

            return redirect()->back();
        }
    }
}
