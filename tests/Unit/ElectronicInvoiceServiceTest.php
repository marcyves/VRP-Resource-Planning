<?php

namespace Tests\Unit;

use App\Contracts\ElectronicInvoicePlatform;
use App\DTO\ElectronicInvoice\PlatformSubmission;
use App\Enums\ElectronicInvoiceStatus;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\School;
use App\Services\ElectronicInvoicing\ElectronicInvoiceService;
use App\Services\ElectronicInvoicing\ElectronicInvoiceValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class ElectronicInvoiceServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_submit_marks_invoice_as_transmitted(): void
    {
        Storage::fake('local');

        $company = Company::factory()->create([
            'siren' => '823059699',
            'address' => '1 rue Test',
            'city' => 'Paris',
            'zip' => '75001',
            'bill_prefix' => 'XDM',
        ]);

        $school = School::factory()->create([
            'company_id' => $company->id,
            'siren' => '123456789',
            'address' => '2 avenue Client',
            'city' => 'Lyon',
            'zip' => '69001',
        ]);

        $invoice = Invoice::create([
            'id' => '26001',
            'description' => 'Test',
            'bill_date' => '2026-06-01',
            'amount' => 1200,
            'company_id' => $company->id,
            'school_id' => $school->id,
            'electronic_invoice_status' => ElectronicInvoiceStatus::Ready,
        ]);

        Storage::put('invoices/XDM26001.pdf', '%PDF-1.4 test');

        $platform = Mockery::mock(ElectronicInvoicePlatform::class);
        $platform->shouldReceive('isConfigured')->andReturn(true);
        $platform->shouldReceive('submitOutbound')
            ->once()
            ->andReturn(new PlatformSubmission(pdpReference: '42', rawResponse: ['id' => 42]));

        $service = new ElectronicInvoiceService($platform, new ElectronicInvoiceValidator);

        $updated = $service->submit($invoice);

        $this->assertSame(ElectronicInvoiceStatus::Transmitted, $updated->electronic_invoice_status);
        $this->assertSame('42', $updated->pdp_reference);
    }
}
