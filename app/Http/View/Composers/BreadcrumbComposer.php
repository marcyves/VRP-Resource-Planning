<?php

namespace App\Http\View\Composers;

use App\Models\School;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BreadcrumbComposer
{
    public function compose(View $view): void
    {
        if (! Auth::check() || ! request()->routeIs('planning.*', 'calendar.*', 'invoice.*')) {
            $view->with('breadcrumbUsesSelectors', false);

            return;
        }

        $isInvoice = request()->routeIs('invoice.*');
        $currentYear = session('current_year', now()->format('Y'));
        $currentSemester = session('current_semester', 'all');

        $breadcrumbSchools = Auth::user()->getSchools();
        $breadcrumbCourses = collect();

        if (! $isInvoice && ($schoolId = session('school_id'))) {
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

        $view->with('breadcrumbUsesSelectors', true);
        $view->with('breadcrumbModule', $isInvoice ? 'invoice' : 'planning');
        $view->with('breadcrumbShowCourse', ! $isInvoice);
        $view->with('breadcrumbSchools', $breadcrumbSchools);
        $view->with('breadcrumbCourses', $breadcrumbCourses);
    }
}
