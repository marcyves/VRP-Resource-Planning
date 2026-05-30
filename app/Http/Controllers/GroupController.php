<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupCourse;
use App\Models\PlanningCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $groups = $user->getGroups();
        $current_year = session('current_year', now()->format('Y'));
        
        $search = request('search');
        $inactiveQuery = $user->getGroupsQuery(false);
        if ($search) {
            $inactiveQuery->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('short_name', 'like', "%$search%");
            });
        }
        $inactive = $inactiveQuery->paginate(15)->withQueryString();

        $list = $groups->map(function(Group $group){
            return $group->id;
        });

        $courses = GroupCourse::whereIn('group_id', $list)
            ->join('courses', 'courses.id', '=', 'group_course.course_id');

        $occurences = $groups->getGroupOccurences($current_year);

        return view('group.index', compact('groups', 'occurences', 'inactive', 'courses', 'current_year'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(String $course_id)
    {
        return view('group.create', compact('course_id'));
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
        $active = false;
        $year = $request->year ?? now()->format('Y');

        if($course_id ==0 && session('course_id') != null){
            $course_id = session('course_id');
            $active = true;
        }

        try{
            $group = Group::create([
                    'name' => $request->name,
                    'short_name' => $request->short_name,
                    'size' => $request->size,
                    'company_id' => $company_id,
                    'active' => $active,
                    'year' => $year,
                ]);

            session()->flash('success', __('messages.group_saved_success'));

            if ($course_id == 0){
                return redirect(route('group.index'));
            }else{

                GroupCourse::create([
                    'group_id' => $group->id,
                    'course_id' => $course_id
                ]);

                return redirect(route('course.show', $course_id));
            }
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
        GroupCourse::create([
            'course_id' => $course_id,
            'group_id' => $group_id
        ]);

        return redirect()->back();
    }

    /**
     * Switch the specified group active status.
     */
    public function switch(String $group_id)
    {
        $group = Group::find($group_id);

        $group->active = !$group->active;
        $group->save();

        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function unlink(String $group_id)
    {
        $course_id = session()->get('course_id');

        $group_link = GroupCourse::where([
            'course_id' => $course_id,
            'group_id' => $group_id
        ])->get()[0];

        $group_link = GroupCourse::findOrFail($group_link->id);
        $group_link->delete();

        session()->flash('success', __('messages.group_released_success'));

        return redirect()->back();

    }

    /**
     * Display the specified resource.
     */
    public function show(Group $group)
    {
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
        $group = Group::find($group_id);
        $user = Auth::user();
        $courses = $user->getCourses(now()->format('Y'), 'all');

        return view('group.edit', compact('group', 'courses'));        
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
            $group = Group::findOrFail($group_id);
            $group->name = $request->name;
            $group->short_name = $request->short_name;
            $group->size = $request->size;
            $group->year = $request->year;
            $group->update();

            //TODO add group_course record to link them

            session()->flash('success', __('messages.group_updated_success'));

            return redirect(route('course.show', $request->course_id));
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
        $group = Group::findOrFail($group_id);

        try{

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
