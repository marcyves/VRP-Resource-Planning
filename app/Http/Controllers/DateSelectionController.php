<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DateSelectionController extends Controller
{
    public function index(Request $request)
    {
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

        return redirect()->back();
    }
}
