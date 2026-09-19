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
        session()->put('last_school_id', $school->id);
        session()->forget('course');
        session()->forget('course_id');

        return redirect()->to($this->planningContextRedirectUrl($request, $school));
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

        return $this->buildPlanning($request, $current_semester, $current_month, $current_year);
    }

    public function previous(Request $request)
    {
        return $this->navigatePlanning($request, -1);
    }

    public function next(Request $request)
    {
        return $this->navigatePlanning($request, 1);
    }

    private function navigatePlanning(Request $request, int $direction)
    {
        $current_semester = Tools::getCurrentSemester($request);
        $current_year = Tools::getCurrentYear($request);
        $current_month = Tools::getCurrentMonth($request);
        $planningView = Tools::getPlanningView($request);
        $weekStart = Tools::getPlanningWeekStart($request, $current_year, $current_month);
        $shifted = Tools::shiftPlanningPeriod($planningView, $weekStart, $current_year, $current_month, $direction);

        session([
            'planning_week_start' => $shifted['week_start']->toDateString(),
            'current_year' => $shifted['year'],
            'current_month' => $shifted['month'],
        ]);

        return $this->buildPlanning($request, $current_semester, $shifted['month'], $shifted['year']);
    }

    private function buildPlanning(Request $request, $current_semester, $current_month, $current_year)
    {

        $current_day = now()->format('d');
        $planningView = Tools::getPlanningView($request);
        $weekStart = Tools::getPlanningWeekStart($request, (int) $current_year, (int) $current_month);

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

        if ($planningView === 'week') {
            $weekEndExclusive = $weekStart->copy()->addWeek();
            $planning = Planning::getDetailsBetween(
                $weekStart->format('Y-m-d H:i:s'),
                $weekEndExclusive->format('Y-m-d H:i:s')
            );
            $calendarDays = collect(range(0, 6))->map(fn ($offset) => $weekStart->copy()->addDays($offset));
        } else {
            $planning = Planning::getDetails((string) $current_year, (string) $current_month);
            $calendarDays = null;
        }

        $monthly_gain = 0;
        $monthly_hours = 0;
        $billingSchools = [];
        foreach ($planning as $event) {
            $sessionGain = Tools::planningGain($event->begin, $event->end, $event->rate, $event->billable_rate);
            $monthly_hours += $event->session_length;
            $monthly_gain += $sessionGain;

            $schoolId = (int) $event->school_id;
            if (! isset($billingSchools[$schoolId])) {
                $billingSchools[$schoolId] = [
                    'id' => $schoolId,
                    'name' => $event->school_name,
                    'sessions' => 0,
                    'hours' => 0.0,
                    'amount_ht' => 0.0,
                    'unbilled_sessions' => 0,
                    'unbilled_amount_ht' => 0.0,
                ];
            }
            $billingSchools[$schoolId]['sessions']++;
            $billingSchools[$schoolId]['hours'] += (float) $event->session_length;
            $billingSchools[$schoolId]['amount_ht'] += $sessionGain;
            if ($event->invoice_id === null || $event->invoice_id === '') {
                $billingSchools[$schoolId]['unbilled_sessions']++;
                $billingSchools[$schoolId]['unbilled_amount_ht'] += $sessionGain;
            }
        }
        $billingSchools = collect($billingSchools)
            ->map(function (array $school) {
                $school['amount_ttc'] = round($school['amount_ht'] * 1.2, 2);
                $school['unbilled_amount_ttc'] = round($school['unbilled_amount_ht'] * 1.2, 2);
                $school['amount_ht'] = round($school['amount_ht'], 2);
                $school['unbilled_amount_ht'] = round($school['unbilled_amount_ht'], 2);

                return $school;
            })
            ->sortByDesc(fn (array $school) => [$school['unbilled_sessions'], $school['unbilled_amount_ht'], $school['hours']])
            ->values();

        $months = Tools::getMonthNames();         //generate month names according to the current locale
        $weekdays = collect(Carbon::getDays())->map(fn($dayName) => ucfirst(Carbon::create($dayName)->dayName)); //generate day names according to the current locale
        $weekdays->push($weekdays[0]);         // Week starts on Monday
        $weekdays->shift();

        $locale = \App\Support\TerminologyLocale::normalizeBaseLocale(app()->getLocale());
        if ($planningView === 'week') {
            $weekEnd = $weekStart->copy()->addDays(6);
            $start = $weekStart->copy()->locale($locale);
            $end = $weekEnd->copy()->locale($locale);
            $periodTitle = $start->isSameMonth($end)
                ? $start->translatedFormat('j').'–'.$end->translatedFormat('j M Y')
                : $start->translatedFormat('j M').' – '.$end->translatedFormat('j M Y');
        } else {
            $periodTitle = ucfirst(Carbon::create((int) $current_year, (int) $current_month, 1)->locale($locale)->translatedFormat('F')).' '.$current_year;
        }

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
            'billingSchools',
            'planningView',
            'calendarDays',
            'periodTitle',
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
            'hour' => 'nullable|integer|min:8|max:19',
            'minutes' => 'nullable|integer|min:0|max:59',
        ]);

        $courseId = $validated['course'] ?? session('course_id');

        if (! $courseId) {
            return redirect()
                ->route('planning.index')
                ->with('danger', __('messages.planning_select_course_first'));
        }

        $request->session()->put('planning_create_date', $validated['date']);
        $request->session()->put('planning_create_course_id', $courseId);
        $request->session()->put('planning_create_hour', (int) ($validated['hour'] ?? 8));
        $request->session()->put('planning_create_minutes', (int) ($validated['minutes'] ?? 0));

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
        $hour = (int) $request->session()->get('planning_create_hour', 8);
        $minutes = (int) $request->session()->get('planning_create_minutes', 0);

        return view('planning.create', compact('date', 'groups', 'session_length', 'course', 'hour', 'minutes'));
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

    private function planningContextRedirectUrl(Request $request, ?School $school = null): string
    {
        $redirect = $request->input('redirect');

        if (is_string($redirect) && $redirect !== '' && str_starts_with($redirect, url('/'))) {
            if ($school !== null) {
                $rewritten = $this->rewriteSchoolScopedRedirect($redirect, $school);
                if ($rewritten !== null) {
                    return $rewritten;
                }
            }

            return $redirect;
        }

        return route('planning.index');
    }

    /**
     * On school show/edit, changing the breadcrumb school must open that school
     * (not bounce back to the previous school URL which re-binds session).
     */
    private function rewriteSchoolScopedRedirect(string $redirect, School $school): ?string
    {
        $path = parse_url($redirect, PHP_URL_PATH) ?? '';
        $query = parse_url($redirect, PHP_URL_QUERY);

        if (preg_match('#/school/\d+/edit/?$#', $path)) {
            $url = route('school.edit', $school->id);

            return $query ? "{$url}?{$query}" : $url;
        }

        if (preg_match('#/school/\d+/?$#', $path)) {
            $url = route('school.show', $school);

            return $query ? "{$url}?{$query}" : $url;
        }

        return null;
    }
}
