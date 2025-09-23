<?php

namespace App\Http\Controllers;

use App\Classes\invoiceSchool;
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

    public function previous(Request $request){
        $current_year = Tools::getCurrentYear($request);
        $current_month = Tools::getCurrentMonth($request) - 1;

        if($current_month < 1){
            $current_month = 12;
            $current_year -= 1;
            session(['current_year' => $current_year]);
        }
        session(['current_month' => $current_month]);

        return $this->buildBilling($current_month, $current_year, false);    
    }

    public function next(Request $request){
        $current_year = Tools::getCurrentYear($request);
        $current_month = Tools::getCurrentMonth($request) + 1;

        if($current_month > 12){
            $current_month = 1;
            $current_year += 1;
            session(['current_year' => $current_year]);
        }
        session(['current_month' => $current_month]);

        return $this->buildBilling($current_month, $current_year, false); 
    }

    private function buildBilling($current_month, $current_year, $order){
        $current_semester = "all";
        $years = Auth::user()->getSchools()->getYears();
        $months = Tools::getMonthNames();

        $schools = Auth::user()->getSchools();
        $planning = $schools->getBillingPlanning($current_year, $current_month);

        if(!$planning)
        {
            $monthly_hours = 0;
            return view('planning.billing',compact('schools', 'current_year', 'current_month', 'monthly_hours','years', 'months'));
            //            return redirect()->back()
        }

        [$schools, $monthly_gain, $monthly_hours] = Tools::getBillingInformation($planning);

        $bills = Auth::user()->getInvoices();

        if($order){
            return view('planning.billing_by_date',compact('schools', 'current_year', 'current_month', 'monthly_gain', 'monthly_hours', 'bills', 'years', 'months'));    
        }else{
            return view('planning.billing',compact('schools', 'current_year', 'current_month', 'monthly_gain', 'monthly_hours', 'bills', 'years', 'months'));    
        }
    }

}
