<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $groups = Group::all();

        return view('group.index', compact('groups'));
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
            'name' => 'required|max:80',
            'size' => 'required|min:0',
        ]);
        
        try{
            Group::create([
                    'name' => $request->name,
                    'size' => $request->size,
                    'course_id' => $course_id
                ]);
            return redirect(route('course.show', $course_id))
                ->with([
                    'success' => "Groupe enregistré avec succès"]);
        }
        catch (\Exception $e) {
            dd($e);
            return redirect()->back()
            ->with('error', "Erreur lors de l'enregitrement du groupe");
        }               
    }

    /**
     * Display the specified resource.
     */
    public function show(Group $group)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(String $group_id)
    {
        $group = Group::find($group_id);

        return view('group.edit', compact('group'));        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, String $group_id)
    {
        $validated = $request->validate([
            'name' => 'required|max:80',
            'size' => 'required|min:0',
        ]);
        
        try{
            $group = Group::findOrFail($group_id);
            $group->name = $request->name;
            $group->size = $request->size;
            $group->update();
            return redirect(route('course.show', $group->course_id))
                ->with([
                    'success' => "Groupe modifié avec succès"]);
        }
        catch (\Exception $e) {
            dd($e);
            return redirect()->back()
            ->with('error', "Erreur lors de l'enregitrement du groupe");
        }               
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(String $group_id)
    {
        $group = Group::findOrFail($group_id);
        $course_id = $group->course_id;

        $group->delete();

        return redirect(route('course.show', $course_id))
        ->with([
            'success' => "Groupe effacé avec succès"]);
    }
}
