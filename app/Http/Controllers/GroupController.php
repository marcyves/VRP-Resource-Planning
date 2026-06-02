<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Group;
use App\Models\GroupCourse;
use App\Models\PlanningCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GroupController extends Controller
{
    private function companyGroup(string $groupId): Group
    {
        return Group::forCompany(Auth::user()->company_id)->findOrFail($groupId);
    }

    private function companyCourse(int $courseId): Course
    {
        return Course::query()
            ->whereHas('school', fn ($q) => $q->where('company_id', Auth::user()->company_id))
            ->findOrFail($courseId);
    }

    /**
     * Course to link on create: explicit route id, otherwise course in session.
     */
    private function resolveCourseIdForLink(string|int $routeCourseId): ?int
    {
        $id = (int) $routeCourseId;
        if ($id > 0) {
            return $id;
        }

        $sessionCourseId = session('course_id');

        return $sessionCourseId ? (int) $sessionCourseId : null;
    }

    private function linkGroupToCourse(Group $group, int $courseId): void
    {
        $this->companyCourse($courseId);

        GroupCourse::firstOrCreate([
            'group_id' => $group->id,
            'course_id' => $courseId,
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $groups = $user->getGroups();
        $current_year = session('current_year', now()->format('Y'));
        $sessionCourseId = session('course_id');
        $sessionCourseName = session('course');

        $search = request('search');
        $inactiveQuery = $user->getGroupsQuery(false);
        if ($search) {
            $inactiveQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('short_name', 'like', "%$search%");
            });
        }
        $inactive = $inactiveQuery
            ->paginate(15)
            ->withQueryString()
            ->fragment('groups-inactive');

        $groupIds = $groups->pluck('id')
            ->merge($inactive->pluck('id'))
            ->unique()
            ->values();

        $occurences = Group::planningOccurrencesForIds($groupIds, $current_year);

        return view('group.index', compact(
            'groups',
            'occurences',
            'inactive',
            'current_year',
            'sessionCourseId',
            'sessionCourseName',
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(String $course_id)
    {
        $linkCourseId = $this->resolveCourseIdForLink($course_id);
        $linkCourseName = $linkCourseId
            ? (session('course') ?? $this->companyCourse($linkCourseId)->name)
            : null;

        return view('group.create', compact('course_id', 'linkCourseId', 'linkCourseName'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, String $course_id)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'short_name' => 'required|min:3',
            'size' => 'required|min:0',
        ]);
        
        $company_id = Auth::user()->company_id;
        $year = $request->year ?? now()->format('Y');
        $linkCourseId = $this->resolveCourseIdForLink($course_id);

        try{
            $group = Group::create([
                    'name' => $request->name,
                    'short_name' => $request->short_name,
                    'size' => $request->size,
                    'company_id' => $company_id,
                    'active' => true,
                    'year' => $year,
                ]);

            session()->flash('success', __('messages.group_saved_success'));

            if ($linkCourseId === null) {
                return redirect(route('group.index'));
            }

            $this->linkGroupToCourse($group, $linkCourseId);

            return redirect(route('course.show', $linkCourseId));
        }
        catch (\Exception $e) {
            // dd($e);
            session()->flash('danger', __('messages.group_save_error'));

            return redirect()->back();
        }               
    }

    /**
     * Link the specified group to the current course.
     */
    public function link(String $group_id)
    {
        $course_id = session()->get('course_id');

        if (! $course_id) {
            session()->flash('danger', __('messages.group_link_no_course'));

            return redirect()->back();
        }

        $this->companyCourse((int) $course_id);
        $group = $this->companyGroup($group_id);

        GroupCourse::firstOrCreate([
            'course_id' => $course_id,
            'group_id' => $group->id,
        ]);

        session()->flash('success', __('messages.group_linked_success'));

        return redirect()->back();
    }

    /**
     * Switch the specified group active status.
     */
    public function switch(String $group_id)
    {
        $group = $this->companyGroup($group_id);
        $group->active = ! $group->active;
        $group->save();

        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function unlink(String $group_id)
    {
        $course_id = session()->get('course_id');

        $this->companyGroup($group_id);

        GroupCourse::where([
            'course_id' => $course_id,
            'group_id' => $group_id,
        ])->delete();

        session()->flash('success', __('messages.group_released_success'));

        return redirect()->back();

    }

    /**
     * Display the specified resource.
     */
    public function show(Group $group)
    {
        abort_unless($group->company_id === Auth::user()->company_id, 404);

        $courses = $group->getCourses();
        $current_year = session('current_year', now()->format('Y'));
        $occurences = (new PlanningCollection([$group]))->getGroupOccurences($current_year);

        return view('group.show', compact('courses', 'group', 'occurences'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(String $group_id)
    {
        $group = $this->companyGroup($group_id);
        $linkedCourses = $group->getCourses();
        $returnCourseId = session('course_id');

        return view('group.edit', compact('group', 'linkedCourses', 'returnCourseId'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, String $group_id)
    {
        $validated = $request->validate([
            'name' => 'required|max:80',
            'short_name' => 'required|min:3',
            'size' => 'required|min:0',
        ]);
        
        try{
            $group = $this->companyGroup($group_id);
            $group->name = $request->name;
            $group->short_name = $request->short_name;
            $group->size = $request->size;
            $group->year = $request->year;
            $group->update();

            session()->flash('success', __('messages.group_updated_success'));

            if ($request->filled('return_course_id')) {
                return redirect(route('course.show', $request->return_course_id));
            }

            return redirect(route('group.show', $group_id));
        }
        catch (\Exception $e) {
            // dd($e);
            session()->flash('danger', __('messages.group_save_error'));

            return redirect()->back();
        }               
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(String $group_id)
    {
        $group = $this->companyGroup($group_id);

        try {
            $group->delete();

            session()->flash('success', __('messages.group_deleted_success'));
    
        }
        catch (\Exception $e) {
            if($e->getCode() == 23000){
                session()->flash('danger', __('messages.group_delete_in_use'));

            }else{
                // Unknown error
                session()->flash('danger', $e->getMessage());
            }
        }      

        return redirect()->back();
    }
}
