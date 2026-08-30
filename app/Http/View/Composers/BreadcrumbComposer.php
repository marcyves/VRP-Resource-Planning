<?php

namespace App\Http\View\Composers;

use App\Models\School;
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
        )) {
            $view->with('breadcrumbUsesSelectors', false);

            return;
        }

        $isInvoice = request()->routeIs('invoice.*', 'treasury.invoices.*');
        $isPlanning = request()->routeIs('planning.*');

        $breadcrumbSchools = Auth::user()->getSchools();
        $breadcrumbCourses = collect();

        if ($isPlanning && ($schoolId = session('school_id'))) {
            $school = $breadcrumbSchools->firstWhere('id', (int) $schoolId)
                ?? School::query()
                    ->where('company_id', Auth::user()->company_id)
                    ->where('id', $schoolId)
                    ->first();

            if ($school) {
                // List every course for the school — not session current_year, which tracks
                // calendar navigation in the agenda and would hide e.g. a 2026 course in 2027.
                $breadcrumbCourses = $school->getCourses('all');
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
