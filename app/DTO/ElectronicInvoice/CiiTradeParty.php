<?php

namespace App\DTO\ElectronicInvoice;

use App\Models\Company;
use App\Models\School;

readonly class CiiTradeParty
{
    public function __construct(
        public string $name,
        public ?string $siren = null,
        public ?string $address = null,
        public ?string $city = null,
        public ?string $zip = null,
        public ?string $country = null,
        public ?string $vatNumber = null,
        public ?string $electronicAddress = null,
        public bool $isBuyer = false,
    ) {}

    public static function fromCompany(Company $company): self
    {
        return new self(
            name: $company->name,
            siren: self::normalizeSiren($company->siren ?: $company->siret),
            address: $company->address,
            city: $company->city,
            zip: $company->zip,
            country: $company->country,
            vatNumber: $company->vat_number,
        );
    }

    public static function fromSchool(School $school): self
    {
        return new self(
            name: $school->name,
            siren: self::normalizeSiren($school->siren ?: $school->siret),
            address: $school->address,
            city: $school->city,
            zip: $school->zip,
            country: $school->country,
            vatNumber: $school->vat_number,
            electronicAddress: $school->electronic_address,
            isBuyer: true,
        );
    }

    /**
     * @param  array<string, mixed>  $profile
     */
    public static function fromSuperPdpProfile(array $profile): self
    {
        $siren = isset($profile['number']) ? (string) $profile['number'] : null;
        $electronicAddress = self::sandboxElectronicAddress($profile);

        return new self(
            name: (string) ($profile['formal_name'] ?: $profile['trade_name'] ?? ''),
            siren: self::normalizeSiren($siren),
            address: isset($profile['address']) ? (string) $profile['address'] : null,
            city: isset($profile['city']) ? (string) $profile['city'] : null,
            zip: isset($profile['postcode']) ? (string) $profile['postcode'] : null,
            country: isset($profile['country']) ? (string) $profile['country'] : 'FR',
            electronicAddress: $electronicAddress,
        );
    }

    public function withSandboxRouting(string $siren, string $electronicAddress): self
    {
        return new self(
            name: $this->name,
            siren: self::normalizeSiren($siren),
            address: $this->address,
            city: $this->city,
            zip: $this->zip,
            country: $this->country,
            vatNumber: $this->vatNumber,
            electronicAddress: $electronicAddress,
            isBuyer: $this->isBuyer,
        );
    }

    /**
     * @param  array<string, mixed>  $profile
     */
    private static function sandboxElectronicAddress(array $profile): ?string
    {
        if (($profile['env'] ?? '') !== 'sandbox' || ! isset($profile['id'])) {
            return null;
        }

        $prefix = (string) config('electronic-invoicing.superpdp.sandbox_routing_prefix', '315143296');

        return $prefix.'_'.$profile['id'];
    }

    public function resolvedVatNumber(): ?string
    {
        if ($this->vatNumber) {
            return strtoupper(preg_replace('/\s+/', '', $this->vatNumber) ?? '');
        }

        if (! $this->siren) {
            return null;
        }

        $key = (12 + 3 * ((int) $this->siren % 97)) % 97;

        return sprintf('FR%02d%s', $key, $this->siren);
    }

    public function resolvedElectronicAddress(): ?string
    {
        return $this->electronicAddress ?: $this->siren;
    }

    public function countryCode(): string
    {
        $country = $this->country;

        if (! $country || stripos($country, 'france') !== false || strtoupper($country) === 'FR') {
            return 'FR';
        }

        return strtoupper(substr($country, 0, 2));
    }

    private static function normalizeSiren(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $value);

        if (! $digits) {
            return null;
        }

        return strlen($digits) >= 9 ? substr($digits, 0, 9) : $digits;
    }
}
