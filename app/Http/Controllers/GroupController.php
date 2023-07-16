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
        //
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
            return redirect(route('dashboard'))
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
    public function edit(Group $group)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Group $group)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Group $group)
    {
        //
    }
}
