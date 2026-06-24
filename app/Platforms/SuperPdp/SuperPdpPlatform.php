<?php

namespace App\Platforms\SuperPdp;

use App\DTO\ElectronicInvoice\CiiTradeParty;
use App\Contracts\ElectronicInvoicePlatform;
use App\DTO\ElectronicInvoice\PlatformEvent;
use App\DTO\ElectronicInvoice\PlatformSubmission;
use App\Enums\PlatformEventType;
use App\Models\Invoice;
use App\Services\ElectronicInvoicing\ElectronicInvoiceCiiBuilder;
use App\Services\ElectronicInvoicing\ElectronicInvoiceValidator;
use Illuminate\Http\Request;

class SuperPdpPlatform implements ElectronicInvoicePlatform
{
    public function __construct(
        private readonly ?SuperPdpClient $client,
        private readonly ElectronicInvoiceValidator $validator,
        private readonly ElectronicInvoiceCiiBuilder $ciiBuilder,
        private readonly ?string $webhookSecret,
    ) {}

    public function isConfigured(): bool
    {
        return $this->client !== null;
    }

    public function submitOutbound(Invoice $invoice): PlatformSubmission
    {
        if ($this->client === null) {
            throw new \RuntimeException('SuperPDP client is not configured.');
        }

        $externalId = $this->validator->fullInvoiceNumber($invoice);
        $companyProfile = $this->client->companyMe();
        $platformSeller = CiiTradeParty::fromSuperPdpProfile($companyProfile);
        $buyer = $this->resolveBuyerParty($invoice, $companyProfile);
        $ciiXml = $this->ciiBuilder->build($invoice, $platformSeller, $buyer);
        $facturX = $this->client->convertInvoice($ciiXml, 'cii', 'factur-x');

        $body = $this->client->sendInvoicePdf($facturX, $externalId);

        $pdpId = (string) ($body['id'] ?? $body['invoice_id'] ?? '');

        if ($pdpId === '') {
            throw new \RuntimeException('SuperPDP response missing invoice id.');
        }

        return new PlatformSubmission(
            pdpReference: $pdpId,
            rawResponse: $body,
        );
    }

    /**
     * @param  array<string, mixed>  $companyProfile
     */
    private function resolveBuyerParty(Invoice $invoice, array $companyProfile): ?CiiTradeParty
    {
        if (($companyProfile['env'] ?? '') !== 'sandbox') {
            return null;
        }

        $invoice->loadMissing('school');
        $school = $invoice->school;

        if (! $school) {
            return null;
        }

        if ($school->electronic_address) {
            return null;
        }

        if (($companyProfile['env'] ?? '') !== 'sandbox'
            && ! config('electronic-invoicing.superpdp.force_sandbox_buyer', false)) {
            return null;
        }

        $routingSiren = (string) config('electronic-invoicing.superpdp.sandbox_buyer_siren', '000000001');
        $electronicAddress = (string) config(
            'electronic-invoicing.superpdp.sandbox_buyer_electronic_address',
            '315143296_12712',
        );

        return CiiTradeParty::fromSchool($school)->withSandboxRouting($routingSiren, $electronicAddress);
    }

    public function parseWebhook(Request $request): PlatformEvent
    {
        /** @var array<string, mixed> $payload */
        $payload = $request->all();

        $status = strtolower((string) ($payload['status'] ?? $payload['status_code'] ?? ''));
        $pdpReference = isset($payload['invoice_id'])
            ? (string) $payload['invoice_id']
            : (isset($payload['id']) ? (string) $payload['id'] : null);

        $externalId = isset($payload['external_id']) ? (string) $payload['external_id'] : null;

        $type = match (true) {
            str_contains($status, 'reject'), str_contains($status, 'refus') => PlatformEventType::OutboundRejected,
            str_contains($status, 'accept'), str_contains($status, 'valid') => PlatformEventType::OutboundAccepted,
            default => PlatformEventType::OutboundSubmitted,
        };

        $reason = isset($payload['status_message'])
            ? (string) $payload['status_message']
            : (isset($payload['message']) ? (string) $payload['message'] : null);

        return new PlatformEvent(
            type: $type,
            pdpReference: $pdpReference,
            vrpInvoiceId: $externalId,
            rejectionReason: $type === PlatformEventType::OutboundRejected ? $reason : null,
        );
    }

    public function verifyWebhook(Request $request): bool
    {
        if (! $this->webhookSecret) {
            return false;
        }

        $signature = $request->header('X-SuperPDP-Signature')
            ?? $request->header('X-Webhook-Signature');

        if (! is_string($signature) || $signature === '') {
            return false;
        }

        $expected = hash_hmac('sha256', $request->getContent(), $this->webhookSecret);

        return hash_equals($expected, $signature);
    }
}
