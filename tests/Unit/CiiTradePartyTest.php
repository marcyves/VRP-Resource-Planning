<?php

namespace Tests\Unit;

use App\DTO\ElectronicInvoice\CiiTradeParty;
use Tests\TestCase;

class CiiTradePartyTest extends TestCase
{
    public function test_from_super_pdp_profile_maps_sandbox_company(): void
    {
        $party = CiiTradeParty::fromSuperPdpProfile([
            'id' => 12713,
            'env' => 'sandbox',
            'number' => '000000002',
            'formal_name' => 'Burger Queen',
            'address' => '809 avenue du Languedoc',
            'postcode' => '12100',
            'city' => 'Millau',
            'country' => 'FR',
        ]);

        $this->assertSame('Burger Queen', $party->name);
        $this->assertSame('000000002', $party->siren);
        $this->assertSame('315143296_12713', $party->resolvedElectronicAddress());
        $this->assertSame('FR', $party->countryCode());
        $this->assertSame('FR18000000002', $party->resolvedVatNumber());
    }

    public function test_with_sandbox_routing_overrides_buyer_peppol_address(): void
    {
        $buyer = CiiTradeParty::fromSchool(new \App\Models\School([
            'name' => 'ELUV',
            'siren' => '356000000',
            'address' => 'Clos belle vue',
            'city' => 'Chancelade',
            'zip' => '24650',
            'country' => 'France',
        ]))->withSandboxRouting('000000001', '315143296_12712');

        $this->assertSame('ELUV', $buyer->name);
        $this->assertSame('000000001', $buyer->siren);
        $this->assertSame('315143296_12712', $buyer->resolvedElectronicAddress());
    }

    public function test_from_school_uses_electronic_address_when_set(): void
    {
        $school = new \App\Models\School([
            'name' => 'Tricatel',
            'siren' => '000000001',
            'electronic_address' => '315143296_12712',
        ]);

        $buyer = CiiTradeParty::fromSchool($school);

        $this->assertSame('315143296_12712', $buyer->resolvedElectronicAddress());
        $this->assertTrue($buyer->isBuyer);
    }
}
