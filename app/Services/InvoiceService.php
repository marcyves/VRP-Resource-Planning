<?php

namespace App\Services;


use App\Classes\InvoiceGenerator;
use App\Models\Invoice;
use App\Models\Planning;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

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

    /**
     * Calcule le prochain ID de facture basé sur la dernière facture de l'utilisateur.
     * Logique:
     * 1. Récupère la dernière facture via $user->getInvoices().
     * 2. Si aucune facture, retourne l'année courante (2 derniers chiffres) + "001".
     * 3. Si facture existante, incrémente la partie numérique pour garder la séquence.
     * 4. Remplace le préfixe année par l'année courante.
     *
     * Exemple: XDM25123 -> XDM26124
     */
    public function calculateNextInvoiceId(User $user): string
    {
        $bills = $user->getInvoices();

        if ($bills->isEmpty()) {
            // Première facture de l'année courante : YY001
            return substr(Carbon::now()->year, -2) . "001";
        }

        $last_bill = $bills->last();
        // L'ID est stocké généralement comme "25123" (string) dans la colonne ID, 
        // ou alors le préfixe est séparé ? 
        // D'après InvoiceController: $invoice_id = $user->company->bill_prefix . $bill_number; 
        // et Invoice::create(['id' => $invoice_id ...])
        // MAIS dans InvoiceController store: 
        // $invoice_id = $request->invoice_id; // Numeric part only ??
        // $invoice_name = $company->bill_prefix . $invoice_id;
        // Invoice::create(['id' => $invoice_id ...])

        // Regardons InvoiceController:
        // index(): $next_bill = substr($bills[$last_bill]->id, -3) + 1; => prend les 3 derniers chiffres
        // create(): $bill_number = $bills->last()->id ?? ...; $bill_number = (int)$bill_number + 1;

        // Si l'ID stocké est "25123"
        // On veut incrémenter le tout: 25123 -> 25124
        // Puis remplacer le 25 par 26 (année courante)

        $last_id = $last_bill->id; // ex: "25123"

        // Incrémenter la séquence globale
        // On suppose que l'ID est numérique (string numeric)
        $next_sequence = (int)$last_id + 1; // 25124

        $next_sequence_str = (string)$next_sequence;

        // Remplacer les 2 premiers chiffres par l'année courante
        $current_year_prefix = substr(Carbon::now()->year, -2);

        // On remplace les 2 premiers char
        // Attention si la longueur change (ex 999 -> 1000) mais peu probable pour des factures annuelles
        // Si on passe de 25999 à 26000 l'incrément a fait le boulot, on force juste le préfixe

        // Mais si on est en 2026 et last est 25123.
        // next = 25124.
        // On veut 26124.

        // On prend la partie "séquence" (tout sauf les 2 premiers chiffres)
        // ATTENTION: Si l'utilisateur a des factures < 100 ? "2501" ? 
        // La convention semble être YY + 3 digits minimum ?
        // index() fait substr(..., -3).

        // Soyons robuste:
        // On incrémente d'abord pour avoir la suite logique du nombre (ex: 25123 -> 25124).
        // Ensuite on force le préfixe année.

        $next_id_str = (string) $next_sequence;

        // Remplacement du préfixe
        // On assume que le préfixe fait toujours 2 caractères (YY)
        $new_id = $current_year_prefix . substr($next_id_str, 2);

        return $new_id;
    }
}
