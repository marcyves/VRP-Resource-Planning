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
        $currentYear = session('current_year', now()->format('Y'));
        $currentSemester = session('current_semester', 'all');

        $breadcrumbSchools = Auth::user()->getSchools();
        $breadcrumbCourses = collect();

        if ($isPlanning && ($schoolId = session('school_id'))) {
            $school = $breadcrumbSchools->firstWhere('id', (int) $schoolId)
                ?? School::query()
                    ->where('company_id', Auth::user()->company_id)
                    ->where('id', $schoolId)
                    ->first();

            if ($school) {
                $breadcrumbCourses = $school->getCourses(
                    $currentYear === 'all' ? 'all' : (string) $currentYear
                );

                if ($currentSemester !== 'all') {
                    $breadcrumbCourses = $breadcrumbCourses->where('semester', $currentSemester)->values();
                }
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
