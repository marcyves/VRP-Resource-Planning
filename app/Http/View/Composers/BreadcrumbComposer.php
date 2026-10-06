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

        $isInvoice = request()->routeIs('invoice.*', 'treasury.invoices.*');
        $isPlanning = request()->routeIs('planning.*');

        // Invoice school filter keeps every client (billing). Planning / workload
        // dropdowns omit schools with no usable courses.
        $breadcrumbSchools = $isInvoice
            ? Auth::user()->getSchools()
            : SchoolCourseOptions::schoolsFor(Auth::user());
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

        $module = match (true) {
            $isInvoice => 'invoice',
            request()->routeIs('planning.*', 'calendar.*') => 'planning',
            default => 'workload',
        };

        $view->with('breadcrumbUsesSelectors', true);
        $view->with('breadcrumbModule', $module);
        $view->with('breadcrumbShowCourse', $isPlanning);
        $view->with('breadcrumbSchools', $breadcrumbSchools);
        $view->with('breadcrumbCourses', $breadcrumbCourses);
    }
}
