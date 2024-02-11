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
        $bill_id = Auth::user()->getCompanyBillPrefix() . substr(Carbon::now()->year, -2);
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
            return redirect(route('bill.index'))
                ->with([
                    'success' => "Facture enregistrée avec succès"]);
        }
        catch (\Exception $e) {
            dd($e);
            return redirect()->back()
            ->with('error', "Erreur lors de l'enregitrement de la facture");
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
