<?php

namespace Tests\Unit;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\School;
use App\Services\ElectronicInvoicing\ElectronicInvoiceCiiBuilder;
use App\Enums\ElectronicInvoiceStatus;
use Tests\TestCase;

class ElectronicInvoiceCiiBuilderTest extends TestCase
{
    public function test_builds_cross_industry_invoice_xml(): void
    {
        $company = new Company([
            'name' => 'XDM Consulting',
            'bill_prefix' => 'XDM',
            'siren' => '823053699',
            'address' => '237 Bis avenue des Pins',
            'city' => 'Pessac',
            'zip' => '33600',
            'country' => 'France',
        ]);

        $school = new School([
            'name' => 'ELUV',
            'siren' => '356000000',
            'address' => 'Clos belle vue',
            'city' => 'Chancelade',
            'zip' => '24650',
            'country' => 'France',
        ]);
        $school->id = 31;

        $invoice = new Invoice([
            'id' => '26001',
            'description' => 'Formation test',
            'bill_date' => '2026-05-15',
            'amount' => 1200,
            'company_id' => 1,
            'school_id' => 31,
            'electronic_invoice_status' => ElectronicInvoiceStatus::Ready,
        ]);
        $invoice->setRelation('company', $company);
        $invoice->setRelation('school', $school);

        $xml = (new ElectronicInvoiceCiiBuilder)->build($invoice);

        $this->assertStringContainsString('CrossIndustryInvoice', $xml);
        $this->assertStringContainsString('XDM26001', $xml);
        $this->assertStringContainsString('823053699', $xml);
        $this->assertStringContainsString('356000000', $xml);
        $this->assertStringContainsString('Formation test', $xml);
        $this->assertStringContainsString('<CrossIndustryInvoice xmlns="urn:un:unece:uncefact:data:standard:CrossIndustryInvoice:100">', $xml);
        $this->assertStringNotContainsString('rsm:', $xml);
        $this->assertStringNotContainsString('ram:', $xml);
        $this->assertStringContainsString('SubjectCode', $xml);
        $this->assertStringContainsString('>PMT<', $xml);
        $this->assertStringContainsString('DueDateDateTime', $xml);
        $this->assertStringContainsString('URIUniversalCommunication', $xml);
        $this->assertStringContainsString('schemeID="0225"', $xml);
        $this->assertStringContainsString('schemeID="VA"', $xml);
        $this->assertStringContainsString('ApplicableHeaderTradeDelivery', $xml);
    }
}
