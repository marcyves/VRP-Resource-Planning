<?php

namespace Tests\Feature;

use App\Contracts\ElectronicInvoicePlatform;
use App\DTO\ElectronicInvoice\PlatformSubmission;
use App\Enums\ElectronicInvoiceStatus;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\School;
use App\Models\User;
use Database\Seeders\StatusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class ElectronicInvoiceSubmitButtonTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(StatusSeeder::class);
    }

    public function test_treasury_shows_e_button_for_unpaid_ready_in_edit_mode(): void
    {
        [$user, $invoice] = $this->makeContext(ElectronicInvoiceStatus::Ready);
        $this->bindConfiguredPlatform();

        $this->actingAs($user)
            ->get(route('treasury.invoices.index'))
            ->assertOk()
            ->assertSee('icon--e-invoice', false)
            ->assertSee(route('invoice.submitElectronic', $invoice->id), false);
    }

    public function test_school_show_shows_e_button_for_unpaid_ready_in_edit_mode(): void
    {
        [$user, $invoice] = $this->makeContext(ElectronicInvoiceStatus::Ready);
        $this->bindConfiguredPlatform();

        $this->actingAs($user)
            ->get(route('school.show', $invoice->school_id))
            ->assertOk()
            ->assertSee('icon--e-invoice', false)
            ->assertSee(route('invoice.submitElectronic', $invoice->id), false);
    }

    public function test_hides_e_button_for_draft_and_rejected_on_treasury_and_school(): void
    {
        [$user, $draft] = $this->makeContext(ElectronicInvoiceStatus::Draft, invoiceId: '26011');
        $rejected = $this->makeInvoice(
            $draft->company,
            School::find($draft->school_id),
            ElectronicInvoiceStatus::Rejected,
            '26012',
        );
        $this->bindConfiguredPlatform();

        foreach ([
            route('treasury.invoices.index'),
            route('school.show', $draft->school_id),
        ] as $url) {
            $html = $this->actingAs($user)
                ->get($url)
                ->assertOk()
                ->getContent();

            $this->assertStringNotContainsString('icon--e-invoice', $html);
            $this->assertStringNotContainsString(route('invoice.submitElectronic', $draft->id), $html);
            $this->assertStringNotContainsString(route('invoice.submitElectronic', $rejected->id), $html);
        }
    }

    public function test_school_show_hides_e_button_when_platform_is_off(): void
    {
        [$user, $invoice] = $this->makeContext(ElectronicInvoiceStatus::Ready);

        $this->actingAs($user)
            ->get(route('school.show', $invoice->school_id))
            ->assertOk()
            ->assertDontSee('icon--e-invoice', false)
            ->assertDontSee(route('invoice.submitElectronic', $invoice->id), false);
    }

    public function test_school_show_hides_e_button_when_company_opt_in_is_off(): void
    {
        [$user, $invoice] = $this->makeContext(ElectronicInvoiceStatus::Ready);
        $invoice->company->electronic_invoicing_enabled = false;
        $invoice->company->save();
        $this->bindConfiguredPlatform();

        $this->actingAs($user)
            ->get(route('school.show', $invoice->school_id))
            ->assertOk()
            ->assertDontSee('icon--e-invoice', false);
    }

    public function test_hides_e_button_for_paid_ready_on_treasury_and_school(): void
    {
        [$user, $invoice] = $this->makeContext(ElectronicInvoiceStatus::Ready, paid: true);
        $this->bindConfiguredPlatform();

        $this->actingAs($user)
            ->get(route('treasury.invoices.index'))
            ->assertOk()
            ->assertDontSee('icon--e-invoice', false)
            ->assertDontSee(route('invoice.submitElectronic', $invoice->id), false);

        $this->actingAs($user)
            ->get(route('school.show', $invoice->school_id))
            ->assertOk()
            ->assertDontSee('icon--e-invoice', false)
            ->assertDontSee(route('invoice.submitElectronic', $invoice->id), false);
    }

    public function test_hides_e_button_for_transmitted_and_accepted(): void
    {
        [$user, $transmitted] = $this->makeContext(ElectronicInvoiceStatus::Transmitted, invoiceId: '26021');
        $accepted = $this->makeInvoice(
            $transmitted->company,
            School::find($transmitted->school_id),
            ElectronicInvoiceStatus::Accepted,
            '26022',
        );
        $this->bindConfiguredPlatform();

        $html = $this->actingAs($user)
            ->get(route('treasury.invoices.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString(route('invoice.submitElectronic', $transmitted->id), $html);
        $this->assertStringNotContainsString(route('invoice.submitElectronic', $accepted->id), $html);
        $this->assertStringNotContainsString('icon--e-invoice', $html);
    }

    public function test_hides_e_button_in_browse_mode(): void
    {
        [$user, $invoice] = $this->makeContext(ElectronicInvoiceStatus::Ready, mode: 'Browse');
        $this->bindConfiguredPlatform();

        $this->actingAs($user)
            ->get(route('treasury.invoices.index'))
            ->assertOk()
            ->assertDontSee('icon--e-invoice', false);

        $this->actingAs($user)
            ->get(route('school.show', $invoice->school_id))
            ->assertOk()
            ->assertDontSee('icon--e-invoice', false);
    }

    public function test_submitting_ready_from_treasury_transmits(): void
    {
        [$user, $invoice] = $this->makeContext(ElectronicInvoiceStatus::Ready);
        $this->putInvoicePdf($invoice);
        $this->bindConfiguredPlatform(expectSubmit: true);

        $this->actingAs($user)
            ->from(route('treasury.invoices.index'))
            ->post(route('invoice.submitElectronic', $invoice->id))
            ->assertRedirect(route('treasury.invoices.index'))
            ->assertSessionHas('success');

        $fresh = $invoice->fresh();
        $this->assertSame(ElectronicInvoiceStatus::Transmitted, $fresh->electronic_invoice_status);
        $this->assertSame('99', $fresh->pdp_reference);
    }

    public function test_submitting_ready_from_school_show_transmits(): void
    {
        [$user, $invoice] = $this->makeContext(ElectronicInvoiceStatus::Ready);
        $this->putInvoicePdf($invoice);
        $this->bindConfiguredPlatform(expectSubmit: true);

        $this->actingAs($user)
            ->from(route('school.show', $invoice->school_id))
            ->post(route('invoice.submitElectronic', $invoice->id))
            ->assertRedirect(route('school.show', $invoice->school_id))
            ->assertSessionHas('success');

        $fresh = $invoice->fresh();
        $this->assertSame(ElectronicInvoiceStatus::Transmitted, $fresh->electronic_invoice_status);
        $this->assertSame('99', $fresh->pdp_reference);
    }

    public function test_submitting_draft_does_not_promote_or_transmit(): void
    {
        [$user, $invoice] = $this->makeContext(ElectronicInvoiceStatus::Draft);
        $this->putInvoicePdf($invoice);
        $this->bindConfiguredPlatform(expectSubmit: false);

        $this->actingAs($user)
            ->from(route('treasury.invoices.index'))
            ->post(route('invoice.submitElectronic', $invoice->id))
            ->assertRedirect(route('treasury.invoices.index'));

        $fresh = $invoice->fresh();
        $this->assertSame(ElectronicInvoiceStatus::Draft, $fresh->electronic_invoice_status);
        $this->assertNull($fresh->pdp_reference);
    }

    /**
     * @return array{0: User, 1: Invoice}
     */
    private function makeContext(
        ElectronicInvoiceStatus $status,
        bool $paid = false,
        string $mode = 'Edit',
        string $invoiceId = '26001',
    ): array {
        $company = Company::factory()->create([
            'siren' => '823059699',
            'address' => '1 rue Test',
            'city' => 'Paris',
            'zip' => '75001',
            'bill_prefix' => 'XDM',
            'electronic_invoicing_enabled' => true,
        ]);

        $school = School::query()->create([
            'name' => 'Client',
            'company_id' => $company->id,
            'siren' => '123456789',
            'address' => '2 avenue Client',
            'city' => 'Lyon',
            'zip' => '69001',
        ]);

        $user = User::factory()->create([
            'company_id' => $company->id,
            'status_id' => 1,
            'mode' => $mode,
            'password' => 'password',
        ]);

        $invoice = $this->makeInvoice($company, $school, $status, $invoiceId, $paid);

        return [$user, $invoice];
    }

    private function makeInvoice(
        Company $company,
        School $school,
        ElectronicInvoiceStatus $status,
        string $invoiceId,
        bool $paid = false,
    ): Invoice {
        return Invoice::create([
            'id' => $invoiceId,
            'description' => 'Test',
            'bill_date' => now()->toDateString(),
            'amount' => 1200,
            'paid_at' => $paid ? now() : null,
            'company_id' => $company->id,
            'school_id' => $school->id,
            'electronic_invoice_status' => $status,
        ]);
    }

    private function putInvoicePdf(Invoice $invoice): void
    {
        Storage::fake('local');
        $invoice->loadMissing('company');
        Storage::put('invoices/'.$invoice->company->bill_prefix.$invoice->id.'.pdf', '%PDF-1.4 test');
    }

    private function bindConfiguredPlatform(bool $expectSubmit = false): void
    {
        $platform = Mockery::mock(ElectronicInvoicePlatform::class);
        $platform->shouldReceive('isConfigured')->andReturn(true);

        if ($expectSubmit) {
            $platform->shouldReceive('submitOutbound')
                ->once()
                ->with(Mockery::on(function (Invoice $invoice) {
                    return $invoice->electronic_invoice_status === ElectronicInvoiceStatus::Ready;
                }))
                ->andReturn(new PlatformSubmission(pdpReference: '99', rawResponse: ['id' => 99]));
        } else {
            $platform->shouldReceive('submitOutbound')->never();
        }

        $this->app->instance(ElectronicInvoicePlatform::class, $platform);
    }
}
