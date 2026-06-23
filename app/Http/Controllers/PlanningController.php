<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\Course;
use App\Models\Group;
use App\Models\GroupCourse;
use App\Models\Planning;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Http\Utility\Tools;

class PlanningController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    /**
     * Clear school/course context and return to the agenda school selection (all schools).
     */
    public function schools()
    {
        session()->forget('course');
        session()->forget('course_id');
        session()->forget('school');
        session()->forget('school_id');

        return redirect()->route('planning.index');
    }

    public function selectSchool(Request $request)
    {
        $validated = $request->validate([
            'school_id' => 'required|exists:schools,id',
        ]);

        $school = Auth::user()->getSchools()->firstWhere('id', (int) $validated['school_id']);

        if (! $school) {
            abort(403);
        }

        session()->put('school', $school->name);
        session()->put('school_id', $school->id);
        session()->forget('course');
        session()->forget('course_id');

        return redirect()->to($this->planningContextRedirectUrl($request));
    }

    public function selectCourse(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'nullable|exists:courses,id',
        ]);

        if (empty($validated['course_id'])) {
            session()->forget('course');
            session()->forget('course_id');

            return redirect()->to($this->planningContextRedirectUrl($request));
        }

        $course = Course::findOrFail($validated['course_id']);
        $courseSchool = $course->getSchool();

        if ($courseSchool->company_id !== Auth::user()->company_id) {
            abort(403);
        }

        $this->syncPlanningBreadcrumbContext($course);

        return redirect()->to($this->planningContextRedirectUrl($request));
    }

    public function index(Request $request)
    {

        $current_semester = Tools::getCurrentSemester($request);
        $current_year = Tools::getCurrentYear($request);
        $current_month = Tools::getCurrentMonth($request);

        return $this->buildPlanning($current_semester, $current_month, $current_year);
    }

    public function previous(Request $request)
    {
        $current_semester = Tools::getCurrentSemester($request);
        $current_year = Tools::getCurrentYear($request);
        $current_month = Tools::getCurrentMonth($request);

        $current_month -= 1;
        if ($current_month < 1) {
            $current_month = 12;
            $current_year -= 1;
            session(['current_year' => $current_year]);
        }
        session(['current_month' => $current_month]);

        return $this->buildPlanning($current_semester, $current_month, $current_year);
    }

    public function next(Request $request)
    {

        $current_semester = Tools::getCurrentSemester($request);
        $current_year = Tools::getCurrentYear($request);
        $current_month = Tools::getCurrentMonth($request);

        $current_month += 1;
        if ($current_month > 11) {
            $current_month -= 12;
            $current_year += 1;
            session(['current_year' => $current_year]);
        }
        session(['current_month' => $current_month]);

        return $this->buildPlanning($current_semester, $current_month, $current_year);
    }

    private function buildPlanning($current_semester, $current_month, $current_year)
    {

        $current_day = now()->format('d');

        $schools = Auth::user()->getSchools();

        if ($school_id = session()->get('school_id')) {
            $years = Course::where('school_id', $school_id)
                ->select(['year'])
                ->distinct()
                ->orderBy('year', 'asc')
                ->get();
        } else {
            $years = $schools->getYears();
        }
        // Collect Planning information for display
        //$planning = $schools->getPlanning($current_year, $current_month);
        $planning = Planning::getDetails($current_year, $current_month);

        $monthly_gain = 0;
        $monthly_hours = 0;
        foreach ($planning as $event) {
            $monthly_hours += $event->session_length;
            $monthly_gain += $event->session_length * $event->rate;
        }

        $months = Tools::getMonthNames();         //generate month names according to the current locale
        $weekdays = collect(Carbon::getDays())->map(fn($dayName) => ucfirst(Carbon::create($dayName)->dayName)); //generate day names according to the current locale
        $weekdays->push($weekdays[0]);         // Week starts on Monday
        $weekdays->shift();

        return view('planning.index', compact(
            'planning',
            'years',
            'months',
            'weekdays',
            'current_year',
            'current_month',
            'current_day',
            'monthly_gain',
            'monthly_hours',
        ));
    }

    /**
     * Accept course/date selection and redirect to the create form (PRG).
     */
    public function startCreate(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'course' => 'nullable|exists:courses,id',
        ]);

        $courseId = $validated['course'] ?? session('course_id');

        if (! $courseId) {
            return redirect()
                ->route('planning.index')
                ->with('danger', __('messages.planning_select_course_first'));
        }

        $request->session()->put('planning_create_date', $validated['date']);
        $request->session()->put('planning_create_course_id', $courseId);

        return redirect()->route('planning.create');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        if ($request->old('date')) {
            $request->session()->put('planning_create_date', $request->old('date'));
        }
        if ($request->old('course')) {
            $request->session()->put('planning_create_course_id', $request->old('course'));
        }

        $date = $request->session()->get('planning_create_date');
        $courseId = $request->session()->get('planning_create_course_id');

        if (! $date || ! $courseId) {
            return redirect()->route('planning.index');
        }

        $course = Course::findOrFail($courseId);
        $school = $course->getSchool();

        session()->put('course', $course->name);
        session()->put('course_id', $course->id);
        session()->put('school', $school->name);
        session()->put('school_id', $school->id);

        $groups = $course->getLinkedGroups(true);
        $session_length = $course->session_length;

        return view('planning.create', compact('date', 'groups', 'session_length', 'course'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $group_id = (int) $request->group;
        $course_id = (int) $request->course;

        if ($course_id === 0 && session('course_id') !== null) {
            $course_id = (int) session('course_id');
        }

        $planningFields = $request->validate([
            'date' => 'required|date',
            'hour' => 'required',
            'minutes' => 'required',
            'session_length' => 'required',
            'course' => 'required|exists:courses,id',
        ]);

        $course_id = (int) $planningFields['course'];
        $session_length = $planningFields['session_length'];
        $date = $planningFields['date'];
        $hour = $planningFields['hour'];
        $minutes = $planningFields['minutes'];

        if ($group_id === 0) {
            $groupFields = $request->validate([
                'name' => 'required|max:80',
                'short_name' => 'required|min:3',
                'size' => 'required|min:0',
                'year' => 'nullable|digits:4',
            ]);

            try {
                $group = Group::create([
                    'name' => $groupFields['name'],
                    'short_name' => $groupFields['short_name'],
                    'size' => $groupFields['size'],
                    'company_id' => Auth::user()->company_id,
                    'year' => $groupFields['year'] ?? now()->format('Y'),
                    'active' => true,
                ]);

                GroupCourse::create([
                    'group_id' => $group->id,
                    'course_id' => $course_id,
                ]);

                $group_id = $group->id;

                session()->flash('success', __('messages.group_saved_success'));
            } catch (\Exception $e) {
                session()->flash('danger', __('messages.group_save_error'));

                return redirect()->route('planning.create')->withInput();
            }
        } else {
            $request->validate([
                'group' => 'required|integer|exists:groups,id',
            ]);
            $group_id = (int) $request->group;
        }

        session(['current_year' => substr($date, 0, 4)]);
        session(['current_month' => substr($date, 5, 2)]);
        session(['current_day' => substr($date, -2)]);

        $begin = date('Y-m-d H:i:s', strtotime("$date $hour:$minutes:0"));
        //TODO session length is bugged
        $add_hours = intval($session_length);
        $add_minutes = ($session_length - $add_hours) * 60;
        $end = date('Y-m-d H:i:s', strtotime("$date $hour:$minutes:0 +$add_hours hours +$add_minutes minutes"));

        session()->remove('course');
        session()->remove('course_id');

        try {
            Planning::create([
                'begin' => $begin,
                'end' => $end,
                'location' => 'na',
                'group_id' => $group_id,
                'course_id' => $course_id,
            ]);

            session()->flash('success', __('messages.planning_session_saved_success', ['date' => $begin]));

            return redirect(route('planning.index'));
        } catch (\Exception $e) {
            session()->flash('danger', __('messages.planning_session_save_error'));

            return redirect()->route('planning.create')->withInput();
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(Planning $planning)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(String $id)
    {
        $planning = Planning::findOrFail($id);
        $current_group = Group::find($planning->group_id);
        $course = Course::findOrFail($planning->course_id);
        $this->syncPlanningBreadcrumbContext($course);
        $groups = $course->getLinkedGroups(true);
        if ($current_group && $groups->where('id', $current_group->id)->isEmpty()) {
            $groups = $groups->push($current_group);
        }
        $courses = Auth::user()->getCourses();

        $months = Tools::getMonthNames();
        $beginYear = (int) Carbon::parse($planning->begin)->year;
        $years = range($beginYear - 2, $beginYear + 2);

        return view('planning.edit', compact('planning', 'current_group', 'groups', 'courses', 'months', 'years'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Int $id)
    {
        try {
            $planning = Planning::findOrFail($id);
            if ($planning->invoice_id) {
                session()->flash('danger', __('messages.session_locked_by_invoice'));

                return redirect()->back();
            }

            $session_length = $planning->GetSessionLength();

            $day = $request->day;
            $month = $request->month;
            $year = $request->year;

            $date = "$year-$month-$day";

            $hour = $request->hour;
            $minutes = $request->minutes;

            $end_hour = $request->end_hour;
            $end_minutes = $request->end_minutes;

            $begin = date('Y-m-d H:i:s', strtotime("$date $hour:$minutes:0"));
            //TODO session length is bugged
            $end = date('Y-m-d H:i:s', strtotime("$date $end_hour:$end_minutes:0"));

            $planning->begin = $begin;
            $planning->end = $end;
            $planning->group_id = $request->group_id;
            $planning->course_id = $request->course_id;
            $planning->billable_rate = $request->billable_rate;

            $planning->save();

            $this->syncPlanningBreadcrumbContext(Course::findOrFail($planning->course_id));

            session()->flash('success', __('messages.planning_session_updated_success'));
        } catch (\Exception $e) {
            session()->flash('danger', __('messages.planning_session_update_error'));
            //session()->flash('danger', $e->getMessage());
            return redirect()->back();
        }
        $schoolId = session('school_id');

        if ($schoolId) {
            return redirect()->route('school.show', $schoolId)->withFragment('billing');
        }

        return redirect()->route('planning.index');
    }

    /**
     * Duplicate a session to tomorrow, next week, or a custom date.
     */
    public function duplicate(Request $request, string $id)
    {
        $validated = $request->validate([
            'offset' => 'required|in:tomorrow,next_week,custom',
            'date' => 'required_if:offset,custom|nullable|date',
        ]);

        try {
            $source = Planning::findOrFail($id);

            if ($source->invoice_id) {
                session()->flash('danger', __('messages.session_locked_by_invoice'));

                return redirect()->back();
            }

            $begin = Carbon::parse($source->begin);
            $end = Carbon::parse($source->end);

            $newBegin = match ($validated['offset']) {
                'tomorrow' => $begin->copy()->addDay(),
                'next_week' => $begin->copy()->addWeek(),
                'custom' => Carbon::parse($validated['date'])->setTimeFromTimeString($begin->format('H:i:s')),
            };

            $newEnd = match ($validated['offset']) {
                'tomorrow' => $end->copy()->addDay(),
                'next_week' => $end->copy()->addWeek(),
                'custom' => $newBegin->copy()->addMinutes($begin->diffInMinutes($end)),
            };

            $collision = Planning::where('group_id', $source->group_id)
                ->where('begin', '<', $newEnd)
                ->where('end', '>', $newBegin)
                ->exists();

            if ($collision) {
                session()->flash('danger', __('messages.planning_session_duplicate_collision'));

                return redirect()->back();
            }

            Planning::create([
                'begin' => $newBegin->format('Y-m-d H:i:s'),
                'end' => $newEnd->format('Y-m-d H:i:s'),
                'location' => $source->location,
                'group_id' => $source->group_id,
                'course_id' => $source->course_id,
                'billable_rate' => $source->billable_rate,
            ]);

            session(['current_year' => $newBegin->year]);
            session(['current_month' => $newBegin->month]);

            session()->flash('success', __('messages.planning_session_duplicated_success', [
                'date' => $newBegin->format('d/m/Y H:i'),
            ]));
        } catch (\Exception $e) {
            session()->flash('danger', __('messages.planning_session_duplicate_error'));

            return redirect()->back();
        }

        return redirect()->route('planning.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $planning = Planning::findOrFail($id);
            if ($planning->invoice_id) {
                session()->flash('danger', __('messages.session_locked_by_invoice'));

                return redirect()->back();
            }

            $planning->delete();

            session()->flash('success', __('messages.planning_session_deleted_success'));
        } catch (\Exception $e) {
            session()->flash('danger', __('messages.planning_session_delete_error'));
            //session()->flash('danger', $e->getMessage());
            return redirect()->back();
        }

        return redirect(route('planning.index'));
    }

    private function syncPlanningBreadcrumbContext(Course $course): void
    {
        $school = $course->getSchool();

        session()->put('course', $course->name);
        session()->put('course_id', $course->id);
        session()->put('school', $school->name);
        session()->put('school_id', $school->id);
    }

    private function planningContextRedirectUrl(Request $request): string
    {
        $redirect = $request->input('redirect');

        if (is_string($redirect) && $redirect !== '' && str_starts_with($redirect, url('/'))) {
            return $redirect;
        }

        return route('planning.index');
    }
}
