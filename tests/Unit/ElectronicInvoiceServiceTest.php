<?php

namespace Tests\Unit;

use App\Contracts\ElectronicInvoicePlatform;
use App\DTO\ElectronicInvoice\PlatformEvent;
use App\DTO\ElectronicInvoice\PlatformSubmission;
use App\Enums\ElectronicInvoiceStatus;
use App\Enums\PlatformEventType;
use App\Exceptions\ElectronicInvoiceException;
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

        $school = School::query()->create([
            'name' => 'Client',
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

    public function test_submit_is_blocked_when_live_pa_lock_is_on(): void
    {
        config([
            'electronic-invoicing.platform' => 'superpdp',
            'electronic-invoicing.allow_production' => false,
            'electronic-invoicing.superpdp.env' => 'production',
        ]);

        $platform = Mockery::mock(ElectronicInvoicePlatform::class);
        $platform->shouldReceive('isConfigured')->never();
        $platform->shouldReceive('submitOutbound')->never();

        $service = new ElectronicInvoiceService($platform, new ElectronicInvoiceValidator);

        $this->expectException(ElectronicInvoiceException::class);
        $this->expectExceptionMessage(__('messages.electronic_invoice_production_blocked'));

        $service->submit(new Invoice);
    }

    public function test_apply_event_marks_accepted_and_rejected(): void
    {
        $invoice = $this->makeReadyInvoice();
        $invoice->pdp_reference = '42';
        $invoice->electronic_invoice_status = ElectronicInvoiceStatus::Transmitted;
        $invoice->save();

        $platform = Mockery::mock(ElectronicInvoicePlatform::class);
        $service = new ElectronicInvoiceService($platform, new ElectronicInvoiceValidator);

        $accepted = $service->applyEvent(new PlatformEvent(
            type: PlatformEventType::OutboundAccepted,
            pdpReference: '42',
        ));

        $this->assertSame(ElectronicInvoiceStatus::Accepted, $accepted->electronic_invoice_status);

        $rejected = $service->applyEvent(new PlatformEvent(
            type: PlatformEventType::OutboundRejected,
            pdpReference: '42',
            rejectionReason: 'SIREN invalide',
        ));

        $this->assertSame(ElectronicInvoiceStatus::Rejected, $rejected->electronic_invoice_status);
        $this->assertSame('SIREN invalide', $rejected->rejection_reason);
    }

    public function test_apply_event_returns_null_when_invoice_is_unknown(): void
    {
        $platform = Mockery::mock(ElectronicInvoicePlatform::class);
        $service = new ElectronicInvoiceService($platform, new ElectronicInvoiceValidator);

        $this->assertNull($service->applyEvent(new PlatformEvent(
            type: PlatformEventType::OutboundAccepted,
            pdpReference: 'missing',
        )));
    }

    private function makeReadyInvoice(): Invoice
    {
        $company = Company::factory()->create([
            'siren' => '823059699',
            'address' => '1 rue Test',
            'city' => 'Paris',
            'zip' => '75001',
            'bill_prefix' => 'XDM',
        ]);

        $school = School::query()->create([
            'name' => 'Client',
            'company_id' => $company->id,
            'siren' => '123456789',
            'address' => '2 avenue Client',
            'city' => 'Lyon',
            'zip' => '69001',
        ]);

        return Invoice::create([
            'id' => '26001',
            'description' => 'Test',
            'bill_date' => '2026-06-01',
            'amount' => 1200,
            'company_id' => $company->id,
            'school_id' => $school->id,
            'electronic_invoice_status' => ElectronicInvoiceStatus::Ready,
        ]);
    }
}
