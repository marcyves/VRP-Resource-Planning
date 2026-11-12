<?php

namespace App\Http\View\Composers;

use App\Models\School;
use App\Support\SchoolCourseOptions;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BreadcrumbComposer
{
    public function compose(View $view): void
    {
        if (! Auth::check() || Auth::user()->isSuperAdmin() || request()->routeIs(
            'login',
            'register',
            'welcome',
            'account-request.*',
            'password.*',
            'verification.*',
            'password.confirm',
            'super-admin.*',
            'login-stats.*',
        )) {
            $view->with('breadcrumbUsesSelectors', false);

            return;
        }

        $isPlanning = request()->routeIs('planning.*');

        if (! $isPlanning) {
            $view->with('breadcrumbUsesSelectors', false);

            return;
        }

        $breadcrumbSchools = SchoolCourseOptions::schoolsFor(Auth::user());
        $breadcrumbCourses = collect();

        if ($isPlanning && ($schoolId = session('school_id'))) {
            $school = $breadcrumbSchools->firstWhere('id', (int) $schoolId)
                ?? School::query()
                    ->where('company_id', Auth::user()->company_id)
                    ->where('id', $schoolId)
                    ->first();

            if ($school) {
                // Usable courses for the school — not session current_year, which tracks
                // calendar navigation in the agenda and would hide e.g. a 2026 course in 2027.
                $includeCourseId = session('course_id') ? (int) session('course_id') : null;
                $breadcrumbCourses = SchoolCourseOptions::coursesForSchool(
                    $school,
                    Auth::user(),
                    $includeCourseId
                );
            }
        }

        $module = 'planning';

        $view->with('breadcrumbUsesSelectors', true);
        $view->with('breadcrumbModule', $module);
        $view->with('breadcrumbShowCourse', $isPlanning);
        $view->with('breadcrumbSchools', $breadcrumbSchools);
        $view->with('breadcrumbCourses', $breadcrumbCourses);
    }
}
