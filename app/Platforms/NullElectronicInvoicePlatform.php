<?php

namespace App\Platforms;

use App\Contracts\ElectronicInvoicePlatform;
use App\DTO\ElectronicInvoice\PlatformEvent;
use App\DTO\ElectronicInvoice\PlatformSubmission;
use App\Models\Invoice;
use Illuminate\Http\Request;

class NullElectronicInvoicePlatform implements ElectronicInvoicePlatform
{
    public function isConfigured(): bool
    {
        return false;
    }

    public function submitOutbound(Invoice $invoice): PlatformSubmission
    {
        throw new \RuntimeException('Electronic invoicing platform is not configured.');
    }

    public function parseWebhook(Request $request): PlatformEvent
    {
        throw new \RuntimeException('Electronic invoicing platform is not configured.');
    }

    public function verifyWebhook(Request $request): bool
    {
        return false;
    }
}
