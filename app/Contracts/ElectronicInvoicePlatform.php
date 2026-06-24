<?php

namespace App\Contracts;

use App\DTO\ElectronicInvoice\PlatformEvent;
use App\DTO\ElectronicInvoice\PlatformSubmission;
use App\Models\Invoice;
use Illuminate\Http\Request;

interface ElectronicInvoicePlatform
{
    public function isConfigured(): bool;

    public function submitOutbound(Invoice $invoice): PlatformSubmission;

    public function parseWebhook(Request $request): PlatformEvent;

    public function verifyWebhook(Request $request): bool;
}
