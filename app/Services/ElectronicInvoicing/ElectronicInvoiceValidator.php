<?php

namespace App\Services\ElectronicInvoicing;

use App\Enums\ElectronicInvoiceStatus;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\School;
use Illuminate\Support\Facades\Storage;

class ElectronicInvoiceValidator
{
    /**
     * @return list<string> Translated error messages
     */
    public function validate(Invoice $invoice): array
    {
        $invoice->loadMissing(['company', 'school']);
        $errors = [];

        if ($invoice->electronic_invoice_status !== ElectronicInvoiceStatus::Ready) {
            $errors[] = __('messages.electronic_invoice_submit_status_invalid');
        }

        if ($invoice->paid_at !== null) {
            $errors[] = __('messages.invoice_paid_locked');
        }

        $company = $invoice->company;
        if ($company instanceof Company) {
            $errors = array_merge($errors, $this->validateParty(
                __('messages.electronic_invoice_issuer'),
                $company->siren,
                $company->address,
                $company->city,
                $company->zip,
            ));
        }

        $school = $invoice->school;
        if ($school instanceof School) {
            if (! $school->siren && ! $school->siret) {
                $errors[] = __('messages.electronic_invoice_client_siren_missing', [
                    'name' => $school->name,
                ]);
            }

            $errors = array_merge($errors, $this->validateParty(
                $school->name,
                $school->siren ?: $school->siret,
                $school->address,
                $school->city,
                $school->zip,
            ));
        }

        $pdfPath = $this->pdfPath($invoice);
        if (! Storage::exists($pdfPath)) {
            $errors[] = __('messages.electronic_invoice_pdf_missing');
        }

        return $errors;
    }

    /**
     * @return list<string>
     */
    private function validateParty(
        string $label,
        ?string $siren,
        ?string $address,
        ?string $city,
        ?string $zip,
    ): array {
        $errors = [];

        if (! $siren) {
            $errors[] = __('messages.electronic_invoice_siren_missing', ['name' => $label]);
        }

        if (! $address || ! $city || ! $zip) {
            $errors[] = __('messages.electronic_invoice_address_missing', ['name' => $label]);
        }

        return $errors;
    }

    public function pdfPath(Invoice $invoice): string
    {
        $invoice->loadMissing('company');

        return 'invoices/'.$invoice->company->bill_prefix.$invoice->id.'.pdf';
    }

    public function fullInvoiceNumber(Invoice $invoice): string
    {
        $invoice->loadMissing('company');

        return $invoice->company->bill_prefix.$invoice->id;
    }
}
