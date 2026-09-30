<?php

namespace Tests\Unit;

use App\Enums\ElectronicInvoiceStatus;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\School;
use App\Services\ElectronicInvoicing\ElectronicInvoiceValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ElectronicInvoiceValidatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_ready_invoice_with_legal_data_and_pdf_passes(): void
    {
        Storage::fake('local');
        $invoice = $this->makeInvoice();
        Storage::put('invoices/XDM26001.pdf', '%PDF-1.4 test');

        $this->assertSame([], (new ElectronicInvoiceValidator)->validate($invoice));
    }

    public function test_rejects_draft_and_rejected_status(): void
    {
        Storage::fake('local');
        $invoice = $this->makeInvoice();
        Storage::put('invoices/XDM26001.pdf', '%PDF-1.4 test');

        $invoice->electronic_invoice_status = ElectronicInvoiceStatus::Draft;
        $this->assertContains(
            __('messages.electronic_invoice_submit_status_invalid'),
            (new ElectronicInvoiceValidator)->validate($invoice),
        );

        $invoice->electronic_invoice_status = ElectronicInvoiceStatus::Rejected;
        $this->assertContains(
            __('messages.electronic_invoice_submit_status_invalid'),
            (new ElectronicInvoiceValidator)->validate($invoice),
        );
    }

    public function test_rejects_invalid_status_paid_amount_and_identifiers(): void
    {
        Storage::fake('local');

        $company = Company::factory()->create([
            'siren' => '123',
            'siret' => '123',
            'address' => null,
            'city' => null,
            'zip' => null,
            'bill_prefix' => 'XDM',
        ]);

        $school = School::query()->create([
            'name' => 'Client',
            'company_id' => $company->id,
            'siren' => '12',
            'siret' => null,
            'address' => null,
            'city' => null,
            'zip' => null,
        ]);

        $invoice = Invoice::create([
            'id' => '26002',
            'description' => 'Test',
            'bill_date' => null,
            'amount' => 0,
            'paid_at' => now(),
            'company_id' => $company->id,
            'school_id' => $school->id,
            'electronic_invoice_status' => ElectronicInvoiceStatus::Draft,
        ]);

        $errors = (new ElectronicInvoiceValidator)->validate($invoice);

        $this->assertNotEmpty($errors);
        $this->assertContains(__('messages.electronic_invoice_submit_status_invalid'), $errors);
        $this->assertTrue(collect($errors)->contains(fn (string $message) => str_contains($message, 'SIREN')));
        $this->assertTrue(collect($errors)->contains(fn (string $message) => str_contains($message, 'SIRET')));
    }

    public function test_rejects_missing_company_school_and_pdf(): void
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
            'id' => '26003',
            'description' => 'Test',
            'bill_date' => '2026-06-01',
            'amount' => 1200,
            'company_id' => $company->id,
            'school_id' => $school->id,
            'electronic_invoice_status' => ElectronicInvoiceStatus::Ready,
        ]);

        $errors = (new ElectronicInvoiceValidator)->validate($invoice);
        $this->assertTrue(collect($errors)->contains(fn (string $message) => str_contains($message, 'PDF')));

        $invoice->setRelation('company', null);
        $invoice->setRelation('school', null);

        $errors = (new ElectronicInvoiceValidator)->validate($invoice);
        $this->assertTrue(collect($errors)->contains(fn (string $message) => str_contains($message, 'société') || str_contains($message, 'company') || str_contains($message, 'émetteur') || str_contains($message, 'Issuer')));
        $this->assertTrue(collect($errors)->contains(fn (string $message) => str_contains($message, 'client') || str_contains($message, 'Client')));
    }

    public function test_accepts_issuer_siret_when_siren_is_absent(): void
    {
        Storage::fake('local');

        $company = Company::factory()->create([
            'siren' => null,
            'siret' => '82305969900012',
            'address' => '1 rue Test',
            'city' => 'Paris',
            'zip' => '75001',
            'bill_prefix' => 'XDM',
        ]);

        $school = School::query()->create([
            'name' => 'Client',
            'company_id' => $company->id,
            'siren' => null,
            'siret' => '12345678900011',
            'address' => '2 avenue Client',
            'city' => 'Lyon',
            'zip' => '69001',
        ]);

        $invoice = Invoice::create([
            'id' => '26004',
            'description' => 'Test',
            'bill_date' => '2026-06-01',
            'amount' => 1200,
            'company_id' => $company->id,
            'school_id' => $school->id,
            'electronic_invoice_status' => ElectronicInvoiceStatus::Ready,
        ]);

        Storage::put('invoices/XDM26004.pdf', '%PDF-1.4 test');

        $this->assertSame([], (new ElectronicInvoiceValidator)->validate($invoice));
    }

    private function makeInvoice(): Invoice
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
