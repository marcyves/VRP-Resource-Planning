<?php

namespace App\Services\ElectronicInvoicing;

use App\Contracts\ElectronicInvoicePlatform;
use App\DTO\ElectronicInvoice\PlatformEvent;
use App\Enums\ElectronicInvoiceStatus;
use App\Enums\PlatformEventType;
use App\Exceptions\ElectronicInvoiceException;
use App\Models\Company;
use App\Models\Invoice;
use App\Platforms\SuperPdp\SuperPdpConfig;
use Carbon\Carbon;

class ElectronicInvoiceService
{
    public function __construct(
        private readonly ElectronicInvoicePlatform $platform,
        private readonly ElectronicInvoiceValidator $validator,
        private readonly ElectronicInvoiceMonitor $monitor,
    ) {}

    public function platformConfigured(): bool
    {
        return $this->platform->isConfigured();
    }

    public function enabledForCompany(?Company $company): bool
    {
        return $this->platformConfigured()
            && $company instanceof Company
            && $company->allowsElectronicInvoicing();
    }

    public function submit(Invoice $invoice): Invoice
    {
        if (config('electronic-invoicing.platform') === 'superpdp' && SuperPdpConfig::isProductionBlocked()) {
            $this->monitor->warning('e-invoice submit blocked by production lock', [
                'invoice_id' => $invoice->id,
            ]);

            throw new ElectronicInvoiceException(__('messages.electronic_invoice_production_blocked'));
        }

        if (! $this->platform->isConfigured()) {
            throw new ElectronicInvoiceException(__('messages.electronic_invoice_platform_not_configured'));
        }

        $invoice->loadMissing('company');

        if (! $invoice->company instanceof Company || ! $invoice->company->allowsElectronicInvoicing()) {
            throw new ElectronicInvoiceException(__('messages.electronic_invoice_company_disabled'));
        }

        $errors = $this->validator->validate($invoice);
        if ($errors !== []) {
            throw new ElectronicInvoiceException(
                __('messages.electronic_invoice_validation_failed'),
                $errors,
            );
        }

        $this->promoteToReadyBeforeSubmit($invoice);

        $submission = $this->platform->submitOutbound($invoice);

        $invoice->electronic_invoice_status = ElectronicInvoiceStatus::Transmitted;
        $invoice->pdp_reference = $submission->pdpReference;
        $invoice->electronic_status_at = Carbon::now();
        $invoice->rejection_reason = null;
        $invoice->save();

        $this->monitor->info('e-invoice submitted', [
            'invoice_id' => $invoice->id,
            'company_id' => $invoice->company_id,
            'pdp_reference' => $submission->pdpReference,
            'platform' => SuperPdpConfig::platform(),
        ]);

        return $invoice->fresh(['company', 'school']);
    }

    public function applyEvent(PlatformEvent $event): ?Invoice
    {
        $invoice = $this->findInvoiceForEvent($event);

        if (! $invoice) {
            return null;
        }

        match ($event->type) {
            PlatformEventType::OutboundSubmitted => $this->markTransmitted($invoice, $event),
            PlatformEventType::OutboundAccepted => $this->markAccepted($invoice),
            PlatformEventType::OutboundRejected => $this->markRejected($invoice, $event->rejectionReason),
            PlatformEventType::InboundReceived => null,
        };

        $this->monitor->info('e-invoice webhook applied', [
            'invoice_id' => $invoice->id,
            'company_id' => $invoice->company_id,
            'event' => $event->type->value,
            'pdp_reference' => $event->pdpReference,
        ]);

        return $invoice->fresh(['company', 'school']);
    }

    private function promoteToReadyBeforeSubmit(Invoice $invoice): void
    {
        $status = $invoice->electronic_invoice_status ?? ElectronicInvoiceStatus::Draft;

        if ($status !== ElectronicInvoiceStatus::Draft && $status !== ElectronicInvoiceStatus::Rejected) {
            return;
        }

        $invoice->electronic_invoice_status = ElectronicInvoiceStatus::Ready;
        $invoice->electronic_status_at = Carbon::now();
        $invoice->rejection_reason = null;
        $invoice->save();
    }

    private function findInvoiceForEvent(PlatformEvent $event): ?Invoice
    {
        if ($event->pdpReference) {
            $byReference = Invoice::query()
                ->where('pdp_reference', $event->pdpReference)
                ->first();

            if ($byReference) {
                return $byReference;
            }
        }

        if (! $event->vrpInvoiceId) {
            return null;
        }

        $numericId = preg_replace('/\D/', '', $event->vrpInvoiceId) ?: $event->vrpInvoiceId;

        return Invoice::query()->where('id', $numericId)->first();
    }

    private function markTransmitted(Invoice $invoice, PlatformEvent $event): void
    {
        $invoice->electronic_invoice_status = ElectronicInvoiceStatus::Transmitted;
        $invoice->pdp_reference = $event->pdpReference ?? $invoice->pdp_reference;
        $invoice->electronic_status_at = Carbon::now();
        $invoice->save();
    }

    private function markAccepted(Invoice $invoice): void
    {
        $invoice->electronic_invoice_status = ElectronicInvoiceStatus::Accepted;
        $invoice->electronic_status_at = Carbon::now();
        $invoice->rejection_reason = null;
        $invoice->save();
    }

    private function markRejected(Invoice $invoice, ?string $reason): void
    {
        $invoice->electronic_invoice_status = ElectronicInvoiceStatus::Rejected;
        $invoice->electronic_status_at = Carbon::now();
        $invoice->rejection_reason = $reason;
        $invoice->save();
    }
}
