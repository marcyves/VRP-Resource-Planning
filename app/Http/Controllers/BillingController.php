<?php

namespace App\Http\Controllers;

use App\Models\Planning;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Http\Utility\Tools;

class BillingController extends Controller
{
    public function previous(School $school, Request $request)
    {
        $billingYear = Tools::getBillingYear($request);
        $currentMonth = Tools::getCurrentMonth($request) - 1;

        if ($currentMonth < 1) {
            $currentMonth = 12;
            $billingYear -= 1;
        }

        session([
            'billing_year' => $billingYear,
            'current_month' => $currentMonth,
        ]);

        return redirect()->route('school.show', $school)->withFragment('billing');
    }

    public function next(School $school, Request $request)
    {
        $billingYear = Tools::getBillingYear($request);
        $currentMonth = Tools::getCurrentMonth($request) + 1;

        if ($currentMonth > 12) {
            $currentMonth = 1;
            $billingYear += 1;
        }

        session([
            'billing_year' => $billingYear,
            'current_month' => $currentMonth,
        ]);

        return redirect()->route('school.show', $school)->withFragment('billing');
    }

    public function toggleByDate(School $school)
    {
        session(['school_billing_by_date' => ! session('school_billing_by_date', false)]);

        return redirect()->route('school.show', $school)->withFragment('billing');
    }

    public function jumpToUnbilled(School $school, Request $request)
    {
        $billingYear = Tools::getBillingYear($request);
        $currentMonth = Tools::getCurrentMonth($request);
        $previousPeriod = $school->findPreviousUnbilledPeriod($billingYear, $currentMonth);

        if ($previousPeriod) {
            session([
                'billing_year' => $previousPeriod['year'],
                'current_month' => $previousPeriod['month'],
            ]);
        }

        return redirect()->route('school.show', $school)->withFragment('billing');
    }

    public function setBill(Request $request, School $school)
    {
        $month = $request->month;
        $year = $request->year;

        $start_date = trim($year).'-'.substr('0'.trim($month), -2).'-0 00:00:00';
        $month++;
        $end_year = $year;

        if ($month == '13') {
            $month = '01';
            $end_year++;
        }

        $end_date = trim($end_year).'-'.substr('0'.trim($month), -2).'-0 00:00:00';

        $planning_list = Planning::getPlanningBySchoolAndDate($school->id, $start_date, $end_date);

        foreach ($planning_list as $id) {
            $planning = Planning::find($id['id']);
            $planning->invoice_id = Auth()->user()->company->bill_prefix.$request->invoice_id;
            $planning->update();
        }

        session()->flash('success', __('messages.billing_invoice_saved_success'));

        return redirect()->route('school.show', $school)->withFragment('billing');
    }

    public function billing(Request $request)
    {
        return $this->redirectLegacyBilling($request);
    }

    public function byDate(Request $request)
    {
        session(['school_billing_by_date' => true]);

        return $this->redirectLegacyBilling($request);
    }

    public function legacyPrevious(Request $request)
    {
        $billingYear = Tools::getBillingYear($request);
        $currentMonth = Tools::getCurrentMonth($request) - 1;

        if ($currentMonth < 1) {
            $currentMonth = 12;
            $billingYear -= 1;
        }

        session([
            'billing_year' => $billingYear,
            'current_month' => $currentMonth,
        ]);

        return $this->redirectLegacyBilling($request);
    }

    public function legacyNext(Request $request)
    {
        $billingYear = Tools::getBillingYear($request);
        $currentMonth = Tools::getCurrentMonth($request) + 1;

        if ($currentMonth > 12) {
            $currentMonth = 1;
            $billingYear += 1;
        }

        session([
            'billing_year' => $billingYear,
            'current_month' => $currentMonth,
        ]);

        return $this->redirectLegacyBilling($request);
    }

    public function legacySetBill(Request $request)
    {
        $school = School::findOrFail($request->school_id);

        return $this->setBill($request, $school);
    }

    private function redirectLegacyBilling(Request $request): \Illuminate\Http\RedirectResponse
    {
        $schoolId = session('school_id');

        if ($schoolId) {
            return redirect()->route('school.show', $schoolId)->withFragment('billing');
        }

        return redirect()->route('school.index');
    }
}
