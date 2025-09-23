<?php

namespace App\Http\Controllers;

use App\Classes\InvoicePdf;
use App\Models\Invoice;
use App\Models\School;

use App\Http\Utility\Tools;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $bills = $user->getInvoices();

        if ($bills->isNotEmpty($bills)) {
            $last_bill = $bills->keys()->last();
            $next_bill = substr($bills[$last_bill]->id, -3) + 1;
        } else {
            $next_bill = "001";
        }


        $bill_id = $user->getCompanyBillPrefix() . substr(Carbon::now()->year, -2) . $next_bill;
        $company = $user->getCompany();
        $schools = $user->getSchools();

        return view('invoice.index', compact('bills', 'bill_id', 'company', 'schools'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $school_id = $request->school_id;
        $course_id = $request->course_id;
        $month = $request->month;
        $year = $request->year;
        $cmd = $request->cmd;

        $user = Auth::user();
        $bills = $user->getInvoices();
        $bill_number = $bills->last()->id ?? substr(Carbon::now()->year, -2) . "000";

        $bill_number = (int) $bill_number + 1;
        $bill_id = $user->getCompanyBillPrefix() . $bill_number;

        $company = $user->getCompany();
        $school = School::find($school_id);

        if ($cmd == "detailed") {
            [$items, $total_amount ]= Tools::getInvoiceDetails($school_id, $month, $year);
        } else {
            $items = [];
        }

        return view('invoice.create', compact('bill_id', 'bill_number', 'company', 'school', 'items', 'month', 'year', 'total_amount'));
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

        $month = $request->month;
        $year = $request->year;

        $company  =  Auth::user()->getCompany();
        $invoice_id =  $request->id;                           // This is the numeric part only
        $invoice_name = $company->bill_prefix . $invoice_id;     // This is the full ID with the company prefix
        $school = School::find($request->school_id);

        [$items, $total_amount] = Tools::getInvoiceDetails($school->id, $month, $year);

        try {
            $pdfPath = $this->generateAndSaveInvoice($invoice_id, $company, $school, $items);
        } catch (\Exception $e) {
            // Handle PDF generation errors
            dd($e);
            session()->flash('danger', "Erreur lors de la génération de la facture.");
            return redirect()->back();
        }

        try {
            Invoice::create([
                'id' => $invoice_id,
                'description' => $request->description,
                'company_id' => $company->id,
                'school_id' => $request->school_id,
                'amount' => $total_amount,
            ]);

            session()->flash('success', "Facture " . $invoice_name . " enregistrée avec succès.");

            return redirect(route('invoice.index'));
        } catch (\Exception $e) {
            dd($e);
            session()->flash('danger', "Erreur lors de l'enregitrement de la facture.");

            return redirect()->back();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $bill)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Invoice $bill)
    {
        return view('invoice.edit', compact('bill'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invoice $bill)
    {
        $validated = $request->validate([
            'id' => 'required',
            'description' => 'required'
        ]);

        try {
            $bill->description = $request->description;
            $bill->amount = $request->amount;
            $bill->created_at = $request->created_at;
            $bill->paid_at = $request->paid_at;

            $bill->save();

            session()->flash('success', 'Facture ' . $request->id . ' modifiée avec succès.');

            return redirect(route('invoice.index'));
        } catch (\Exception $e) {
            // dd($e);

            session()->flash('danger', "Erreur lors de la modification de l'école " . $request->name . '.');

            return redirect()->back();
        }
    }

    public function payed(String $bill_id)
    {
        try {
            $bill = Invoice::findOrFail($bill_id);
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
            session()->flash('success', "Facture " . $invoice->id . " supprimée avec succès.");
            return redirect()->back();
            //            return redirect(route('dashboard'));
        } catch (\Exception $e) {
            session()->flash('danger', "Erreur lors de la suppression de la facture.");
            //session()->flash('danger', $e->getMessage());
            return redirect()->back();
        }
    }

    private function generateAndSaveInvoice($invoiceId, $company, $school, $items)
    {
        // Get the current date
        $date_facture = date('d/m/Y');
        //$date_facture = '08/09/2025';
        $date_echeance = date('d/m/Y', strtotime('+1 day'));
        //$date_echeance = '09/09/2025';

        $pdf = new InvoicePdf($invoiceId, $date_facture, $date_echeance, $school->code); // Now with no global variables

        $x = 10;
        $wpage = 185;
        $currentY = $pdf->prepare($invoiceId, $x, $company, $school);

        $lineHeight = 4;
        $invoice_lines = 28;

        // Draw the invoice details box
        $boxHeight = $lineHeight * 1.5;
        $pdf->Rect($x, $currentY, $wpage, $boxHeight, 'D'); // 'D' = draw only (border)

        $pdf->SetFont('helvetica', '', 9);
        $line = "Désignation";
        $pdf->writeLine($x, $currentY,  $boxHeight, $line, 'L');

        $x_col = $x + 107;
        $pdf->writeLine($x_col / 2 - 4, $currentY,  $boxHeight, "TVA", 'C');
        $pdf->Rect($x_col, $currentY, 16, $lineHeight * $invoice_lines + $boxHeight, 'D'); // 'D' = draw only (border)
        $pdf->writeLine($x_col / 2 + 34, $currentY,  $boxHeight, "P.U. HT", 'C');
        $pdf->writeLine($x_col / 2 + 74, $currentY,  $boxHeight, "Qté", 'C');
        $pdf->Rect($x_col + 40, $currentY, 16, $lineHeight * $invoice_lines + $boxHeight, 'D'); // 'D' = draw only (border)
        $currentY = $pdf->writeLine($x_col / 2 + 114, $currentY,  $boxHeight, "Total HT", 'C');

        // Draw the border for the invoice details box
        $pdf->Rect($x, $currentY, $wpage, $lineHeight * $invoice_lines, 'D'); // 'D' = draw only (border)

        // Add invoice items
        $pdf->SetFont('helvetica', '', 9);

        $invoiceY = $currentY + 1; // Move down for the first item
        $total_invoice = 0;
        foreach ($items as $item) {
            switch($item[4])
            {
            case "T":
                $lineHeight = $pdf->setTitleFont();
            break;
            case "S":
                $lineHeight = $pdf->setSubTitleFont();
            break;
            default:
                $lineHeight = $pdf->setNormalFont();
            }
            $pdf->SetXY($x + 2, $invoiceY);
            $pdf->Cell(108, $lineHeight, $item[0], 0, 0, 'L', false);
            $pdf->setNormalFont();
            $pdf->Cell(12, $lineHeight, $item[1], 0, 0, 'C', false);
            $value2 = $item[2];
            if (is_numeric($value2))
                $pdf->Cell(18, $lineHeight, number_format($value2, 2), 0, 0, 'R', false);
            else
                $pdf->Cell(18, $lineHeight, $value2, 0, 0, 'R', false);
            $value3 = $item[3];
            if (is_numeric($value3))
                $pdf->Cell(22, $lineHeight, number_format($value3, 1), 0, 0, 'R', false);
            else
                $pdf->Cell(22, $lineHeight, $value3, 0, 0, 'R', false);

            if (is_numeric($value2) && is_numeric($value3)) {
                $total = number_format($value2 * $value3, 2);
                $total_invoice += $item[2] * $item[3];
            } else {
                $total = "";
            }

            $pdf->Cell(20, $lineHeight, $total, 0, 1, 'R', false);
            $invoiceY += $lineHeight;
        }

        $currentY += $invoice_lines * $lineHeight + 2;
        // Add total line
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->writeLine($x, $currentY,  $lineHeight,  "Conditions de règlement:", 'L');
        $pdf->SetFont('helvetica', '', 9);
        $pdf->writeLine($x + 40, $currentY,  $lineHeight, "À réception", 'L');
        $pdf->writeLine($x + 125, $currentY, $lineHeight, "Total HT", 'L');
        $currentY = $pdf->writeLine($x + 150, $currentY, $lineHeight, number_format($total_invoice, 2),  'R');
        $saveY = $currentY; // Save Y position for RIB
        $pdf->writeLine($x + 125, $currentY, $lineHeight, "Total TVA 20%",  'L');
        $currentY = $pdf->writeLine($x + 150, $currentY, $lineHeight, number_format($total_invoice * 0.2, 2),  'R');
        $pdf->Rect($x + 125, $currentY, 60, $lineHeight, 'F'); // 'F' = fill
        $pdf->writeLine($x + 125, $currentY, $lineHeight, "Total TTC",  'L');
        $currentY = $pdf->writeLine($x + 150, $currentY, $lineHeight, number_format($total_invoice * 1.2, 2),  'R');


        $RIB = [
            "Règlement par virement sur le compte bancaire suivant:",
            "",
            "Banque: Crédit Agricole",
            "    Code banque      Code guichet       Numéro de compte     Clé",
            "            19106                  00605                  43657887472           74",
            "Titulaire du compte: SAS XDM Consulting",
            "Code IBAN: FR76 1910 6006 0543 6578 8747 274",
            "Code BIC/SWIFT: AGRIFRPP891"
        ];
        $currentY = $saveY; // Reset Y position for client address

        $pdf->SetFont('helvetica', 'B', 8);

        foreach ($RIB as $line) {
            $currentY = $pdf->writeLine($x, $currentY, $lineHeight, $line, 'L');
        }

        // Output PDF

        $pdfPath = __DIR__ . "/../../../public/invoices/" . $invoiceId . ".pdf";
        $pdf->Output($pdfPath, 'F');
        return $pdfPath;
    }
}
