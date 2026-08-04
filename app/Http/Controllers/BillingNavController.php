<?php

namespace App\Http\Controllers;

use App\Models\School;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class BillingNavController extends Controller
{
    /**
     * Sidebar shortcut: open current/last school billing section, or home with a hint.
     */
    public function __invoke(): RedirectResponse
    {
        $schoolId = session('school_id') ?? session('last_school_id');

        if ($schoolId) {
            $school = School::query()
                ->whereKey($schoolId)
                ->where('company_id', Auth::user()->company_id)
                ->first();

            if ($school) {
                session()->put('school_id', $school->id);
                session()->put('last_school_id', $school->id);

                return redirect()->to(route('school.show', $school).'?focus=billing#billing');
            }
        }

        return redirect()
            ->route('home')
            ->with('billing_needs_school', true);
    }
}
