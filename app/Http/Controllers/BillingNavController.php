<?php

namespace App\Http\Controllers;

use App\Http\Utility\Tools;
use App\Models\Planning;
use App\Support\PlanningBillingSummary;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BillingNavController extends Controller
{
    public function index(Request $request): View
    {
        $year = (int) Tools::getCurrentYear($request);
        $month = (int) Tools::getCurrentMonth($request);

        return $this->prepareView($year, $month);
    }

    public function previous(Request $request): View
    {
        return $this->shift($request, -1);
    }

    public function next(Request $request): View
    {
        return $this->shift($request, 1);
    }

    private function shift(Request $request, int $direction): View
    {
        $year = (int) Tools::getCurrentYear($request);
        $month = (int) Tools::getCurrentMonth($request);
        $date = Carbon::create($year, $month, 1)->addMonths($direction);

        return $this->prepareView((int) $date->year, (int) $date->month);
    }

    private function prepareView(int $year, int $month): View
    {
        session([
            'current_year' => $year,
            'current_month' => $month,
            'billing_year' => $year,
        ]);

        $planning = Planning::getDetails((string) $year, (string) $month);
        $summary = PlanningBillingSummary::fromPlanning($planning);
        $schools = $summary['schools']
            ->filter(fn (array $school) => ($school['unbilled_sessions'] ?? 0) > 0)
            ->values();

        $locale = \App\Support\TerminologyLocale::normalizeBaseLocale(app()->getLocale());
        $periodTitle = ucfirst(Carbon::create($year, $month, 1)->locale($locale)->translatedFormat('F')).' '.$year;

        return view('billing.prepare', [
            'schools' => $schools,
            'periodTitle' => $periodTitle,
            'year' => $year,
            'month' => $month,
        ]);
    }
}
