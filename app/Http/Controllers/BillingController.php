<?php

namespace App\Http\Controllers;

use App\Models\Planning;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

use App\Http\Utility\Tools;

class BillingController extends Controller
{
    /**
     * 
     */
    public function billing(Request $request)
    {
        $current_year = Tools::getCurrentYear($request);
        $current_month = Tools::getCurrentMonth($request);

        return $this->buildBilling($current_month, $current_year, false);
    }

    public function byDate(Request $request)
    {
        $current_year = Tools::getCurrentYear($request);
        $current_month = Tools::getCurrentMonth($request);

        return $this->buildBilling($current_month, $current_year, true);
    }

    public function previous(Request $request)
    {
        $current_year = Tools::getCurrentYear($request);
        $current_month = Tools::getCurrentMonth($request) - 1;

        if ($current_month < 1) {
            $current_month = 12;
            $current_year -= 1;
            session(['current_year' => $current_year]);
        }
        session(['current_month' => $current_month]);

        return $this->buildBilling($current_month, $current_year, false);
    }

    public function next(Request $request)
    {
        $current_year = Tools::getCurrentYear($request);
        $current_month = Tools::getCurrentMonth($request) + 1;

        if ($current_month > 12) {
            $current_month = 1;
            $current_year += 1;
            session(['current_year' => $current_year]);
        }
        session(['current_month' => $current_month]);

        return $this->buildBilling($current_month, $current_year, false);
    }

    private function buildBilling($current_month, $current_year, $order)
    {
        $current_semester = "all";
        $years = Auth::user()->getSchools()->getYears();
        $months = Tools::getMonthNames();

        $schools = Auth::user()->getSchools();
        $planning = $schools->getBillingPlanning($current_year, $current_month);
        if (!$planning) {
            $monthly_hours = 0;
            $monthly_gain = 0;
            return view('planning.billing', compact('schools', 'current_year', 'current_month', 'monthly_hours', 'monthly_gain', 'years', 'months'));
            //            return redirect()->back()
        }

        [$schools, $monthly_gain, $monthly_hours] = Tools::getBillingInformation($planning);

        $bills = Auth::user()->getInvoices();

        if ($order) {
            return view('planning.billing_by_date', compact('schools', 'current_year', 'current_month', 'monthly_gain', 'monthly_hours', 'bills', 'years', 'months'));
        } else {
            return view('planning.billing', compact('schools', 'current_year', 'current_month', 'monthly_gain', 'monthly_hours', 'bills', 'years', 'months'));
        }
    }

    /**
     * Display the specified resource.
     */
    public function setBill(Request $request)
    {
        $school_id = $request->school_id;
        $month = $request->month;
        $year  = $request->year;

        $start_date =  trim($year) . "-" . substr("0" . trim($month), -2) . "-0 00:00:00";
        $month++;
        $end_year = $year;

        if ($month == "13") {
            $month = "01";
            $end_year++;
        }

        $end_date   =  trim($end_year) . "-" . substr("0" . trim($month), -2) . "-0 00:00:00";

        $planning_list = Planning::getPlanningBySchoolAndDate($school_id, $start_date, $end_date);

        foreach ($planning_list as $id) {
            $planning = Planning::find($id['id']);
            $planning->invoice_id = Auth()->user()->company->bill_prefix . $request->invoice_id;
            $planning->update();
        }

        session()->flash('success', "Facture enregistrée avec succès.");

        return redirect(route('billing.index'));
    }
}
