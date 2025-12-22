<?php
namespace App\Services;


use App\Classes\InvoiceGenerator;
use App\Models\Invoice;
use App\Models\Planning;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class InvoiceService
{
    /**
     * Génère l'objet PDF complet avec ses données
     */
    public function buildPdf(Invoice $invoice, $lines = null)
    {
        $school = $invoice->school;
        $company = $school->company;

        $pdf = new InvoiceGenerator($invoice);
        $pdf->AddPage();
        $pdf->drawInvoiceContent($company, $school, $lines);

        return $pdf;
    }

    /**
     * Enregistre le PDF sur le disque
     */
    public function saveToDisk(Invoice $invoice, $lines = null)
    {
        $pdf = $this->buildPdf($invoice, $lines);
        $content = $pdf->Output('', 'S'); // 'S' retourne le flux binaire
        
        $path = "invoices/{$invoice->company->bill_prefix}{$invoice->id}.pdf";
        Storage::put($path, $content);

        return $path;
    }

    /**
     * Associe les sessions de planning à une facture pour une période donnée.
     */
    public function linkPlanningToInvoice(string $schoolId, int $month, int $year, string $invoiceId)
    {
        $startDate = sprintf("%04d-%02d-01 00:00:00", $year, $month);

        // Calcul du mois suivant pour la date de fin
        $endMonth = $month + 1;
        $endYear = $year;
        if ($endMonth > 12) {
            $endMonth = 1;
            $endYear++;
        }
        $endDate = sprintf("%04d-%02d-01 00:00:00", $endYear, $endMonth);

        $planningList = Planning::getPlanningBySchoolAndDate($schoolId, $startDate, $endDate);

        foreach ($planningList as $item) {
            $planning = Planning::find($item['id']);
            if ($planning) {
                $planning->invoice_id = Auth::user()->company->bill_prefix . $invoiceId;
                $planning->save();
            }
        }
    }
}