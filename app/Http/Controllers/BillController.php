<?php

namespace App\Http\Controllers;

use App\Classes\InvoicePdf;
use App\Models\Bill;
use App\Models\School;
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

        if ($bills->isNotEmpty($bills)) {
            $last_bill = $bills->keys()->last();
            $next_bill = substr($bills[$last_bill]->id, -3) + 1;
        } else {
            $next_bill = "001";
        }


        $bill_id = $user->getCompanyBillPrefix() . substr(Carbon::now()->year, -2) . $next_bill;
        $company = $user->getCompany();
        $schools = $user->getSchools();

        return view('bills.index', compact('bills', 'bill_id', 'company', 'schools'));
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
        $school = School::find($request->school_id);

        $client = [
            "name" => $school->name,
            "code" =>   $school->code,
            "address" => [
                    $school->address,
                   $school->zip ." ". $school->country,
            ]
        ];

        $items = [
            ["Module Méthodologie d'Audit", "", "", ""],
            ["Cours dispensé en distanciel pour LiveCampus", "20%", "400.00", "5"],
        ];
        // Get the current date
        // $date_facture = date('d/m/Y');
        $date_facture = '08/09/2025';
        //$date_echeance = date('d/m/Y', strtotime('+1 days'));
        $date_echeance = '09/09/2025';

        // Call the function to generate the invoice
        $this->generate_invoice($bill_id, $client, $items);

        try {
            Bill::create([
                'id' => $bill_id,
                'description' => $request->description,
                'company_id' => $company->id,
                'school_id' => $request->school_id,
                'amount' => $request->amount,
            ]);

            session()->flash('success', "Facture $bill_id enregistrée avec succès.");


            return redirect(route('bill.index'));
        } catch (\Exception $e) {
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

        try {
            $bill->description = $request->description;
            $bill->amount = $request->amount;
            $bill->created_at = $request->created_at;
            $bill->paid_at = $request->paid_at;

            $bill->save();

            session()->flash('success', 'Facture ' . $request->id . ' modifiée avec succès.');

            return redirect(route('bill.index'));
        } catch (\Exception $e) {
            // dd($e);

            session()->flash('danger', "Erreur lors de la modification de l'école " . $request->name . '.');

            return redirect()->back();
        }
    }

    public function payed(String $bill_id)
    {
        try {
            $bill = Bill::findOrFail($bill_id);
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
    public function destroy(Bill $bill)
    {
        try {
            $bill->delete();
            session()->flash('success', "Facture " . $bill->id . " supprimée avec succès.");
            return redirect()->back();
            //            return redirect(route('dashboard'));
        } catch (\Exception $e) {
            session()->flash('danger', "Erreur lors de la suppression de la facture.");
            //session()->flash('danger', $e->getMessage());
            return redirect()->back();
        }
    }

    public function generate_invoice($id_facture, $client, $items)
    {
        global $client_name, $client_code, $client_address;
        $client_name = $client['name'];
        $client_code = $client['code'];
        $client_address = $client['address'];

        // Create new PDF document
        $pdf = new InvoicePdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Document info
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('XDM Consulting');
        $pdf->SetTitle("Facture $id_facture");
        $pdf->SetSubject('Facture');

        // set default header data
        $pdf->setHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

        // set header and footer fonts
        $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->setDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->setMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->setHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->setFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->setAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf->SetFillColor(230, 230, 230);
        $pdf->SetTextColor(0, 0, 0);

        // Add a page
        $pdf->AddPage();

        $pdf->SetFont('helvetica', '', 8);

        // Set position for box
        $x = 10;
        $x2 = 100; // Adjusted for the recipient box
        $wpage = 185;
        $y = 36;
        $w = 82;
        $h = 40;
        $invoice_lines = 28;
        $invoiceY = $y + $h;

        // Line height
        $lineHeight = 6;
        $currentY = $y;

        $pdf->SetXY($x, $currentY);
        $pdf->Cell(0, $lineHeight, 'Émetteur', 0, false, 'L', false);

        $pdf->SetXY($x2 + 2, $currentY);
        $pdf->Cell($w, $lineHeight, 'Adressé à', 0, false, 'L', false);

        //$currentY = $y + 2;
        $currentY += $lineHeight;
        // Draw filled rectangle for background
        $pdf->SetFillColor(230, 230, 230);
        $pdf->SetLineWidth(0.1);
        $pdf->Rect($x, $currentY, $w, $h, 'F'); // 'F' = fill
        // Draw the border rectangle only (no fill)
        $pdf->Rect(100, $currentY, $w + 13, $h, 'D'); // 'D' = draw only (border)

        $currentY += 2;
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetXY($x + 2, $currentY);
        $pdf->Cell(0, $lineHeight, 'XDM Consulting', 0, 1, 'L', false);

        $pdf->SetXY($x2 + 2, $currentY);
        $pdf->Cell(0, $lineHeight, $client_name, 0, 1, 'L', false);


        // Normal font for address
        $pdf->SetFont('helvetica', '', 9);
        $addressLines = [
            "237 bis, avenue des Pins",
            "06210 Mandelieu La Napoule"
        ];
        $lineHeight = 4.5;
        $saveY = $currentY; // Save the current Y position
        foreach ($addressLines as $line) {
            $currentY += $lineHeight;
            $pdf->SetXY($x + 2, $currentY);
            $pdf->Cell(0, $lineHeight, $line, 0, 1, 'L', false);
        }


        $currentY = $saveY; // Reset Y position for client address
        foreach ($client_address as $line) {
            $currentY += $lineHeight;
            $pdf->SetXY($x2 + 2, $currentY);
            $pdf->Cell(0, $lineHeight, $line, 0, 1, 'L', false);
        }

        $addressLines = [
            "Tél.: 0624455583",
            "Email: contact@xdm-consulting.fr",
            "Web: https://www.xdm-consulting.fr"
        ];
        $lineHeight = 4;
        $currentY += $lineHeight; // Move down for the next section
        foreach ($addressLines as $line) {
            $currentY += $lineHeight;
            $pdf->SetXY($x + 2, $currentY);
            $pdf->Cell(0, $lineHeight, $line, 0, 1, 'L', false);
        }

        $pdf->SetFont('helvetica', '', 8);
        $currentY = $invoiceY + $lineHeight * 2;

        $line = "Catégorie d'opérations : Prestation de services";
        $currentY = $pdf->writeLine($x, $currentY,  $lineHeight, $line, 'L');
        $line = "Montants exprimés en Euros";
        $pdf->writeLine($x, $currentY,  $lineHeight, $line, 'R');

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
            $pdf->SetXY($x + 2, $invoiceY);
            $pdf->Cell(108, $lineHeight, $item[0], 0, 0, 'L', false);
            $pdf->Cell(12, $lineHeight, $item[1], 0, 0, 'C', false);
            $pdf->Cell(18, $lineHeight, $item[2], 0, 0, 'R', false);
            $pdf->Cell(22, $lineHeight, $item[3], 0, 0, 'R', false);
            $value2 = $item[2];
            $value3 = $item[3];

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
        $pdf->Output(__DIR__ . "/facture_$id_facture.pdf", 'F');
    }
}
