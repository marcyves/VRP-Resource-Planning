<?php

namespace App\Http\Controllers;

use App\Services\InvoiceService;

use App\Models\Invoice;
use App\Models\School;

use App\Http\Utility\Tools;
use App\Models\Planning;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class InvoiceController extends Controller
{
    private $billingService;
    private $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $bills = $user->getInvoices();

        $invoice_id_number = $this->invoiceService->calculateNextInvoiceId($user);
        $invoice_id = $user->company->bill_prefix . $invoice_id_number;
        $company = $user->company;
        $schools = $user->getSchools();

        return view('invoice.index', compact('bills', 'invoice_id', 'company', 'schools'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $school_id = $request->school_id;
        $course_id = $request->course_id;
        $bill_date = date('d/m/Y', strtotime($request->bill_date));
        $month = $request->month;
        $year = $request->year;
        $cmd = $request->cmd;

        $user = Auth::user();
        $bill_number = $this->invoiceService->calculateNextInvoiceId($user);
        $invoice_id = $user->company->bill_prefix . $bill_number;

        $company = $user->company;
        $school = School::find($school_id);

        if ($cmd == "detailed") {
            [$items, $total_amount] = Tools::getInvoiceDetails($school_id, $month, $year, $invoice_id, false);
        } else {
            $items = [];
            $total_amount = 0;
        }

        return view('invoice.create', compact('invoice_id', 'bill_number', 'company', 'school', 'items', 'month', 'year', 'bill_date', 'total_amount'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_id' => 'required',
            'description' => 'required'
        ]);

        $month = $request->month;
        $year = $request->year;
        $bill_date = $request->bill_date;

        $company  =  Auth::user()->company;
        $invoice_id =  $request->invoice_id;                           // This is the numeric part only
        $invoice_name = $company->bill_prefix . $invoice_id;     // This is the full ID with the company prefix
        $school = School::find($request->school_id);

        [$items, $total_amount] = Tools::getInvoiceDetails($school->id, $month, $year, $invoice_name);

        try {
            // 1. Logique de création de l'enregistrement en base de données
            $invoice = Invoice::create([
                'id' => $invoice_id,
                'description' => $request->description,
                'bill_date' => Carbon::createFromFormat('d/m/Y', $bill_date)->format('Y-m-d'),
                'company_id' => $company->id,
                'school_id' => $request->school_id,
                'amount' => $total_amount,
            ]);

            // 2. Génération et enregistrement physique du fichier via le Service
            // Le service retourne le chemin (ex: "invoices/2024/facture_001.pdf")
            $this->invoiceService->saveToDisk($invoice, $items);

            // 4. Lier les sessions de planning à cette facture
            $this->invoiceService->linkPlanningToInvoice(
                $request->school_id,
                $request->month,
                $request->year,
                $invoice->id
            );

            session()->flash('success', "Facture " . $invoice_name . " enregistrée avec succès.");

            return redirect(route('invoice.index'));
        } catch (\Exception $e) {
            dd($e);
            session()->flash('danger', "Erreur lors de l'enregistrement de la facture.");

            return redirect()->back();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(String $bill)
    {
        $user = Auth::user();
        $company = $user->company;

        $file_path = "invoices/{$company->bill_prefix}{$bill}.pdf";
        if (Storage::exists($file_path)) {
            return Storage::download($file_path);
        } else {
            session()->flash('danger', "File not found");
            return redirect()->back();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Invoice $invoice)
    {
        return view('invoice.edit', compact('invoice'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'id' => 'required',
            'description' => 'required'
        ]);

        try {
            $invoice->description = $request->description;
            $invoice->amount = $request->amount;
            $invoice->created_at = $request->created_at;
            $invoice->paid_at = $request->paid_at;

            $invoice->save();

            session()->flash('success', 'Facture ' . $request->id . ' modifiée avec succès.');

            return redirect(route('invoice.index'));
        } catch (\Exception $e) {
            dd($e);

            session()->flash('danger', "Erreur lors de la modification de la facture " . $request->name . '.');

            return redirect()->back();
        }
    }

    public function payed(String $invoice_id)
    {
        try {
            $bill = Invoice::findOrFail($invoice_id);
            $bill->paid_at = Carbon::now();
            $bill->save();
            session()->flash('success', "Facture " . $bill->id . " payée avec succès.");
            return redirect()->back();
        } catch (\Exception $e) {
            session()->flash('danger', "Erreur lors du payement de la facture: " . $e->getMessage());
            return redirect()->back();
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        try {
            $invoice->delete();
            $planning_list = Planning::where('invoice_id', Auth::user()->company->bill_prefix . $invoice->id)->get();

            foreach ($planning_list as $id) {
                $planning = Planning::find($id['id']);
                $planning->invoice_id = "";
                $planning->update();
            }

            session()->flash('success', "Facture " . Auth::user()->company->bill_prefix . $invoice->id . " supprimée avec succès.");
            return redirect()->back();
        } catch (\Exception $e) {
            session()->flash('danger', "Erreur lors de la suppression de la facture.");
            //session()->flash('danger', $e->getMessage());
            return redirect()->back();
        }
    }
}
