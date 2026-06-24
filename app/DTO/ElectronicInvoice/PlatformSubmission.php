<?php

namespace App\DTO\ElectronicInvoice;

readonly class PlatformSubmission
{
    public function __construct(
        public string $pdpReference,
        public array $rawResponse = [],
    ) {}
}
