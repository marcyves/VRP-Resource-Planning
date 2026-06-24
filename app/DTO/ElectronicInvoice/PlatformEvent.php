<?php

namespace App\DTO\ElectronicInvoice;

use App\Enums\PlatformEventType;

readonly class PlatformEvent
{
    public function __construct(
        public PlatformEventType $type,
        public ?string $pdpReference = null,
        public ?string $vrpInvoiceId = null,
        public ?string $rejectionReason = null,
    ) {}
}
