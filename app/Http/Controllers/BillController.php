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
        $user = Auth::user();
        $bills = $user->getBills();

        if($bills->isNotEmpty($bills)){
            $last_bill = $bills->keys()->last();
            $next_bill = substr($bills[$last_bill]->id, -3) +1;
        }else{
            $next_bill = "001";
        }


        $bill_id = $user->getCompanyBillPrefix() . substr(Carbon::now()->year, -2) . $next_bill;
        $company = $user->getCompany();

        return view('bills.index', compact('bills', 'bill_id', 'company'));
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
            'id' => 'required',
            'description' => 'required'
        ]);
        
        $company  =  Auth::user()->getCompany();
        $bill_id =  $request->id;


        try{
            Bill::create([
                    'id' => $bill_id,
                    'description' => $request->description,
                    'company_id' => $company->id,
                    'amount' => $request->amount,
                ]);
                
            session()->flash('success', "Facture $bill_id enregistrée avec succès.");

            return redirect(route('bill.index'));
        }
        catch (\Exception $e) {
            // dd($e);

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
        return view('bills.edit', compact('bill'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Bill $bill)
    {
        $validated = $request->validate([
            'id' => 'required',
            'description' => 'required'
        ]);

        try{
            $bill->description = $request->description;
            $bill->amount = $request->amount;
            $bill->created_at = $request->created_at;
            $bill->paid_at = $request->paid_at;

            $bill->save();

            session()->flash('success', 'Facture '.$request->id.' modifiée avec succès.');

            return redirect(route('bill.index'));
        }
        catch (\Exception $e) {
            // dd($e);

            session()->flash('danger', "Erreur lors de la modification de l'école ".$request->name.'.');

            return redirect()->back();
        }       
    }

    public function payed(String $bill_id)
    {
        try{
            $bill = Bill::findOrFail($bill_id);
            $bill->paid_at = Carbon::now();
            $bill->save();
            session()->flash('success', "Facture ".$bill->id." payée avec succès.");
            return redirect()->back();
        }
        catch (\Exception $e) {
            session()->flash('danger', "Erreur lors du payement de la facture: ".$e->getMessage());
            return redirect()->back();
        }   
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bill $bill)
    {
        try{
            $bill->delete();
            session()->flash('success', "Facture ".$bill->id." supprimée avec succès.");
            return redirect()->back();
//            return redirect(route('dashboard'));
        }
        catch (\Exception $e) {
            session()->flash('danger', "Erreur lors de la suppression de la facture.");
            //session()->flash('danger', $e->getMessage());
            return redirect()->back();
        }   
    }
}
