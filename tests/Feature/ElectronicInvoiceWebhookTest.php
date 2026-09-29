<?php

namespace Tests\Feature;

use App\Contracts\ElectronicInvoicePlatform;
use App\Enums\ElectronicInvoiceStatus;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\School;
use App\Models\User;
use App\Platforms\SuperPdp\SuperPdpPlatform;
use App\Services\ElectronicInvoicing\ElectronicInvoiceCiiBuilder;
use App\Services\ElectronicInvoicing\ElectronicInvoiceValidator;
use Database\Seeders\StatusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ElectronicInvoiceWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(StatusSeeder::class);
    }

    public function test_unknown_platform_returns_404(): void
    {
        $this->postJson(route('webhooks.e-invoice', 'b2brouter'), ['id' => 1])
            ->assertNotFound();
    }

    public function test_missing_signature_returns_401(): void
    {
        $this->bindPlatform('test-secret');

        $this->postJson(route('webhooks.e-invoice', 'superpdp'), ['id' => '42', 'status' => 'accepted'])
            ->assertUnauthorized();
    }

    public function test_valid_hmac_marks_invoice_accepted(): void
    {
        $invoice = $this->makeInvoice();
        $invoice->pdp_reference = '42';
        $invoice->electronic_invoice_status = ElectronicInvoiceStatus::Transmitted;
        $invoice->save();

        $this->bindPlatform('test-secret');

        $payload = json_encode([
            'id' => '42',
            'status' => 'accepted',
            'external_id' => '26001',
        ], JSON_THROW_ON_ERROR);

        $this->call(
            'POST',
            route('webhooks.e-invoice', 'superpdp'),
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_SUPERPDP_SIGNATURE' => hash_hmac('sha256', $payload, 'test-secret'),
            ],
            $payload,
        )->assertNoContent();

        $this->assertSame(
            ElectronicInvoiceStatus::Accepted,
            $invoice->fresh()->electronic_invoice_status,
        );
    }

    public function test_submit_electronic_stays_off_when_platform_is_null(): void
    {
        $invoice = $this->makeInvoice();
        $user = User::factory()->create([
            'company_id' => $invoice->company_id,
            'status_id' => 1,
            'mode' => 'Edit',
            'password' => 'password',
        ]);

        $this->actingAs($user)
            ->from(route('treasury.invoices.index'))
            ->post(route('invoice.submitElectronic', $invoice->id))
            ->assertRedirect();

        $this->assertSame(
            ElectronicInvoiceStatus::Ready,
            $invoice->fresh()->electronic_invoice_status,
        );
        $this->assertNull($invoice->fresh()->pdp_reference);
    }

    private function bindPlatform(string $secret): void
    {
        $this->app->instance(
            ElectronicInvoicePlatform::class,
            new SuperPdpPlatform(
                null,
                new ElectronicInvoiceValidator,
                new ElectronicInvoiceCiiBuilder,
                $secret,
            ),
        );
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
