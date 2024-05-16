<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class BillController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $bills = Bill::all()->sortBy('id');
        $last_bill = $bills->keys()->last();
        $next_bill = substr($bills[$last_bill]->id, -3) +1;

        $bill_id = Auth::user()->getCompanyBillPrefix() . substr(Carbon::now()->year, -2) . $next_bill;

        return view('bills.index', compact('bills', 'bill_id'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|max:5',
        ]);
        
        $company  =  Auth::user()->getCompany();
        $bill_id =  $company->bill_prefix . substr(Carbon::now()->year, -2) . $request->id;


        try{
            Bill::create([
                    'id' => $bill_id,
                    'description' => $request->description,
                    'company_id' => $company->id,
                ]);
                
            session()->flash('success', "Facture $bill_id enregistrée avec succès.");

            return redirect(route('bill.index'));
        }
        catch (\Exception $e) {
            dd($e);

            session()->flash('danger', "Erreur lors de l'enregitrement de la facture.");

            return redirect()->back();
        }               
    }

    /**
     * Display the specified resource.
     */
    public function show(Bill $bill)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Bill $bill)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Bill $bill)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bill $bill)
    {
        //
    }
}
