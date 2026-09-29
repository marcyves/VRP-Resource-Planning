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

        if (! $invoice->bill_date) {
            $errors[] = __('messages.electronic_invoice_bill_date_missing');
        }

        if ((float) $invoice->amount <= 0) {
            $errors[] = __('messages.electronic_invoice_amount_invalid');
        }

        $company = $invoice->company;
        if (! $company instanceof Company) {
            $errors[] = __('messages.electronic_invoice_company_missing');
        } else {
            $errors = array_merge($errors, $this->validateParty(
                __('messages.electronic_invoice_issuer'),
                $company->siren,
                $company->siret,
                $company->address,
                $company->city,
                $company->zip,
                requireIdentifier: true,
            ));

            $pdfPath = $this->pdfPath($invoice);
            if (! Storage::exists($pdfPath)) {
                $errors[] = __('messages.electronic_invoice_pdf_missing');
            }
        }

        $school = $invoice->school;
        if (! $school instanceof School) {
            $errors[] = __('messages.electronic_invoice_client_missing');
        } else {
            $sirenDigits = $this->digits($school->siren);
            $siretDigits = $this->digits($school->siret);

            if ($sirenDigits === '' && $siretDigits === '') {
                $errors[] = __('messages.electronic_invoice_client_siren_missing', [
                    'name' => $school->name,
                ]);
            }

            $errors = array_merge($errors, $this->validateParty(
                $school->name,
                $school->siren,
                $school->siret,
                $school->address,
                $school->city,
                $school->zip,
                requireIdentifier: false,
            ));
        }

        return $errors;
    }

    /**
     * @return list<string>
     */
    private function validateParty(
        string $label,
        ?string $siren,
        ?string $siret,
        ?string $address,
        ?string $city,
        ?string $zip,
        bool $requireIdentifier,
    ): array {
        $errors = [];
        $sirenDigits = $this->digits($siren);
        $siretDigits = $this->digits($siret);

        if ($requireIdentifier && $sirenDigits === '' && $siretDigits === '') {
            $errors[] = __('messages.electronic_invoice_siren_missing', ['name' => $label]);
        }

        if ($sirenDigits !== '' && strlen($sirenDigits) !== 9) {
            $errors[] = __('messages.electronic_invoice_siren_invalid', ['name' => $label]);
        }

        if ($siretDigits !== '' && strlen($siretDigits) !== 14) {
            $errors[] = __('messages.electronic_invoice_siret_invalid', ['name' => $label]);
        }

        if (! $address || ! $city || ! $zip) {
            $errors[] = __('messages.electronic_invoice_address_missing', ['name' => $label]);
        }

        return $errors;
    }

    private function digits(?string $value): string
    {
        if (! $value) {
            return '';
        }

        return preg_replace('/\D/', '', $value) ?: '';
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
