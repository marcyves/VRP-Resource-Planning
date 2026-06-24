<?php

namespace App\Http\Controllers;

use App\Enums\ElectronicInvoiceStatus;
use App\Exceptions\ElectronicInvoiceException;
use App\Http\Utility\Tools;
use App\Models\Invoice;
use App\Models\Planning;
use App\Models\School;
use App\Services\ElectronicInvoicing\ElectronicInvoiceService;
use App\Services\InvoiceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class InvoiceController extends Controller
{
    private $billingService;

    private $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    /**
     * Clear school context and return to the invoice list.
     */
    public function schools()
    {
        session()->forget('course');
        session()->forget('course_id');
        session()->forget('school');
        session()->forget('school_id');

        return redirect()->route('treasury.invoices.index');
    }

    public function selectSchool(Request $request)
    {
        $validated = $request->validate([
            'school_id' => 'required|exists:schools,id',
        ]);

        $school = Auth::user()->getSchools()->firstWhere('id', (int) $validated['school_id']);

        if (! $school) {
            abort(403);
        }

        session()->put('school', $school->name);
        session()->put('school_id', $school->id);
        session()->forget('course');
        session()->forget('course_id');

        $redirect = $request->input('redirect');

        if (is_string($redirect) && $redirect !== '' && str_starts_with($redirect, url('/'))) {
            return redirect()->to($redirect);
        }

        return redirect()->route('treasury.invoices.index');
    }

    /**
     * @deprecated Redirects to treasury invoices list.
     */
    public function index()
    {
        return redirect()->route('treasury.invoices.index', request()->query());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $school_id = $request->school_id ?? session('school_id');

        if (! $school_id) {
            session()->flash('warning', __('messages.invoice_create_requires_school'));

            return redirect()->route('treasury.invoices.index');
        }

        $school = Auth::user()->getSchools()->firstWhere('id', (int) $school_id);

        if (! $school) {
            abort(403);
        }

        $course_id = $request->course_id;
        $bill_date = date('d/m/Y', strtotime($request->bill_date ?? 'now'));
        $month = $request->month ?? date('m');
        $year = $request->year ?? date('Y');
        $cmd = $request->cmd;

        $user = Auth::user();
        $bill_number = $this->invoiceService->calculateNextInvoiceId($user);
        $invoice_id = $user->company->bill_prefix.$bill_number;

        $company = $user->company->load('billingBankAccount.bank');

        if ($cmd == 'detailed') {
            [$items, $total_amount] = Tools::getInvoiceDetails($school->id, $month, $year, $invoice_id, false);
        } else {
            $items = [];
            $total_amount = 0;
        }

        $fromSchoolBilling = $request->boolean('from_school_billing');

        return view('invoice.create', compact('invoice_id', 'bill_number', 'company', 'school', 'items', 'month', 'year', 'bill_date', 'total_amount', 'fromSchoolBilling'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_id' => 'required',
            'description' => 'required',
            'amount' => 'nullable|numeric',
            'month' => 'required|numeric',
            'year' => 'required|numeric',
            'bill_date' => 'required|date_format:d/m/Y',
        ]);

        $month = $request->month;
        $day = $request->day;
        $year = $request->year;
        $bill_date = $request->bill_date;

        $company = Auth::user()->company;
        $invoice_id = $request->invoice_id;                           // This is the numeric part only
        $invoice_name = $company->bill_prefix.$invoice_id;     // This is the full ID with the company prefix
        $school = School::find($request->school_id);

        // 1. Always fetch planning details first to see if this constitutes a valid agenda invoice
        [$items, $calculated_amount] = Tools::getInvoiceDetails($school->id, $month, $year, $invoice_name);

        // 2. If planning items exist, we prefer them (Agenda Invoice)
        // If NO planning items exist, but we have a manual amount, we use that (Manual Invoice)
        if (empty($items) && $request->filled('amount')) {
            $total_amount = $request->amount;

            $items = [
                [$request->description, '', '', '', '', 'T'],
                ['Montant forfaitaire', '20%', $total_amount, 1, 1, 'N'],
            ];
        } else {
            // Use the calculated total from planning
            $total_amount = $calculated_amount;
        }

        $amountTtc = $request->filled('amount')
            ? (float) $request->amount
            : round($calculated_amount * 1.2, 2);

        try {
            // 1. Logique de création de l'enregistrement en base de données
            $invoice = Invoice::create([
                'id' => $invoice_id,
                'description' => $request->description,
                'bill_date' => Carbon::createFromFormat('d/m/Y', $bill_date)->format('Y-m-d'),
                'company_id' => $company->id,
                'school_id' => $request->school_id,
                'amount' => $amountTtc,
                'electronic_invoice_status' => ElectronicInvoiceStatus::Ready,
                'electronic_status_at' => Carbon::now(),
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

            session()->flash('success', __('messages.invoice_saved_success', ['name' => $invoice_name]));

            if ($request->boolean('from_school_billing') && $school) {
                return redirect()->route('school.show', $school)->withFragment('billing');
            }

            return redirect(route('treasury.invoices.index'));
        } catch (\Exception $e) {
            dd($e);
            session()->flash('danger', __('messages.invoice_save_error'));

            return redirect()->back();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $bill)
    {
        $user = Auth::user();
        $company = $user->company;

        $file_path = "invoices/{$company->bill_prefix}{$bill}.pdf";
        if (Storage::exists($file_path)) {
            return Storage::download($file_path);
        } else {
            session()->flash('danger', __('messages.invoice_file_not_found'));

            return redirect()->back();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Invoice $invoice)
    {
        if ($invoice->paid_at) {
            session()->flash('danger', __('messages.invoice_paid_locked'));

            return redirect()->route('treasury.invoices.index');
        }

        return view('invoice.edit', compact('invoice'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invoice $invoice)
    {
        if ($invoice->paid_at) {
            session()->flash('danger', __('messages.invoice_paid_locked'));

            return redirect()->route('treasury.invoices.index');
        }

        $validated = $request->validate([
            'id' => 'required',
            'description' => 'required',
        ]);

        try {
            $invoice->description = $request->description;
            $invoice->amount = round((float) $request->amount * 1.2, 2);
            $invoice->created_at = $request->created_at;
            $invoice->paid_at = $request->paid_at;

            $invoice->save();

            session()->flash('success', __('messages.invoice_updated_success', ['id' => $request->id]));

            return redirect(route('treasury.invoices.index'));
        } catch (\Exception $e) {
            dd($e);

            session()->flash('danger', __('messages.invoice_update_error'));

            return redirect()->back();
        }
    }

    public function payed(string $invoice_id)
    {
        try {
            $bill = Invoice::findOrFail($invoice_id);
            $wasPaid = $bill->paid_at !== null;
            $bill->paid_at = $wasPaid ? null : Carbon::now();
            $bill->save();
            session()->flash('success', $wasPaid
                ? __('messages.invoice_payment_cancelled_success', ['id' => $bill->id])
                : __('messages.invoice_paid_success', ['id' => $bill->id])
            );

            return redirect()->back();
        } catch (\Exception $e) {
            session()->flash('danger', __('messages.invoice_payment_error', ['message' => $e->getMessage()]));

            return redirect()->back();
        }
    }

    public function submitElectronic(Invoice $invoice, ElectronicInvoiceService $electronicInvoiceService)
    {
        $this->authorizeInvoice($invoice);

        try {
            $electronicInvoiceService->submit($invoice);

            session()->flash('success', __('messages.electronic_invoice_submit_success', [
                'id' => Auth::user()->company->bill_prefix.$invoice->id,
            ]));
        } catch (ElectronicInvoiceException $e) {
            session()->flash('danger', $e->getMessage());

            if ($e->errors !== []) {
                session()->flash('warning', implode(' · ', $e->errors));
            }
        } catch (\Throwable $e) {
            session()->flash('danger', __('messages.electronic_invoice_submit_error', [
                'message' => $e->getMessage(),
            ]));
        }

        return redirect()->back();
    }

    private function authorizeInvoice(Invoice $invoice): void
    {
        if ($invoice->company_id !== Auth::user()->company_id) {
            abort(403);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        try {
            if ($invoice->paid_at) {
                session()->flash('danger', __('messages.invoice_paid_locked'));

                return redirect()->back();
            }

            $invoice->delete();
            $planning_list = Planning::where('invoice_id', Auth::user()->company->bill_prefix.$invoice->id)->get();

            foreach ($planning_list as $id) {
                $planning = Planning::find($id['id']);
                $planning->invoice_id = '';
                $planning->update();
            }

            session()->flash('success', __('messages.invoice_deleted_success', [
                'name' => Auth::user()->company->bill_prefix.$invoice->id,
            ]));

            return redirect()->back();
        } catch (\Exception $e) {
            session()->flash('danger', __('messages.invoice_delete_error'));

            // session()->flash('danger', $e->getMessage());
            return redirect()->back();
        }
    }
}
