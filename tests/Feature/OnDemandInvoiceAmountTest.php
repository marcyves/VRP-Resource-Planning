<?php

namespace Tests\Feature;

use App\Classes\InvoiceGenerator;
use App\Http\Utility\Tools;
use App\Models\Company;
use App\Models\Course;
use App\Models\Group;
use App\Models\Invoice;
use App\Models\Planning;
use App\Models\Program;
use App\Models\School;
use App\Models\Status;
use App\Models\User;
use App\Services\InvoiceService;
use Database\Seeders\StatusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OnDemandInvoiceAmountTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(StatusSeeder::class);
        Storage::fake('local');
    }

    public function test_create_form_exposes_live_ht_and_ttc_fields(): void
    {
        $context = $this->makeOnDemandContext();

        $this->actingAs($context['admin'])
            ->get(route('invoice.create', [
                'school_id' => $context['school']->id,
                'month' => 4,
                'year' => 2026,
            ]))
            ->assertOk()
            ->assertSee('name="amount_ht"', false)
            ->assertSee('name="amount_ttc"', false)
            ->assertSee('x-bind:value="ht"', false)
            ->assertSee('x-bind:value="ttc"', false)
            ->assertSee('fromHt()', false)
            ->assertSee('fromTtc()', false)
            ->assertSee(__('messages.invoice_amount_ht'), false)
            ->assertSee(__('messages.invoice_amount_ttc'), false)
            ->assertSee(__('messages.invoice_amount_vat_hint', ['rate' => Tools::vatRateLabel()]), false)
            ->assertDontSee('name="amount"', false);
    }

    public function test_store_converts_typed_ttc_to_ht_on_pdf_line_and_keeps_ttc_total(): void
    {
        $context = $this->makeOnDemandContext();

        $this->actingAs($context['admin'])
            ->post(route('invoice.store'), $this->storePayload($context, [
                'description' => 'Prestation hors agenda',
                'amount_ttc' => 120,
            ]))
            ->assertRedirect(route('treasury.invoices.index'));

        $invoice = Invoice::query()->first();
        $this->assertNotNull($invoice);
        $this->assertEqualsWithDelta(120.0, (float) $invoice->amount, 0.02);
        $this->assertEqualsWithDelta(100.0, $invoice->amountHt(), 0.02);
        $this->assertEqualsWithDelta(120.0, $invoice->amountTtc(), 0.02);

        $lines = app(InvoiceService::class)->resolveLines($invoice);
        $this->assertCount(1, $lines);
        $this->assertSame('Prestation hors agenda', $lines[0][0]);
        $this->assertSame('20%', $lines[0][1]);
        $this->assertEqualsWithDelta(100.0, (float) $lines[0][2], 0.001);
        $this->assertSame(1, $lines[0][3]);
        $this->assertSame('T', $lines[0][5]);

        $this->assertPdfLineIsHtNotTtc($invoice, 'Prestation hors agenda', '100.00', '120.00');
    }

    public function test_store_uses_typed_ht_and_persists_computed_ttc(): void
    {
        $context = $this->makeOnDemandContext();

        $this->actingAs($context['admin'])
            ->post(route('invoice.store'), $this->storePayload($context, [
                'description' => 'Prestation HT',
                'amount_ht' => 100,
            ]))
            ->assertRedirect(route('treasury.invoices.index'));

        $invoice = Invoice::query()->first();
        $this->assertEqualsWithDelta(120.0, (float) $invoice->amount, 0.02);

        $lines = app(InvoiceService::class)->resolveLines($invoice);
        $this->assertEqualsWithDelta(100.0, (float) $lines[0][2], 0.001);

        $this->assertPdfLineIsHtNotTtc($invoice, 'Prestation HT', '100.00', '120.00');
    }

    public function test_store_accepts_legacy_amount_as_ttc(): void
    {
        $context = $this->makeOnDemandContext();

        $this->actingAs($context['admin'])
            ->post(route('invoice.store'), $this->storePayload($context, [
                'description' => 'Legacy TTC',
                'amount' => 120,
            ]))
            ->assertRedirect(route('treasury.invoices.index'));

        $invoice = Invoice::query()->first();
        $this->assertEqualsWithDelta(120.0, (float) $invoice->amount, 0.02);
        $lines = app(InvoiceService::class)->resolveLines($invoice);
        $this->assertEqualsWithDelta(100.0, (float) $lines[0][2], 0.001);
    }

    public function test_store_rejects_zero_and_negative_amounts(): void
    {
        $context = $this->makeOnDemandContext();

        foreach ([
            ['amount_ttc' => 0],
            ['amount_ht' => 0],
            ['amount' => 0],
            ['amount_ttc' => -12],
            ['amount_ht' => -10],
            ['amount' => -5],
            [],
        ] as $amounts) {
            Invoice::query()->delete();

            $this->actingAs($context['admin'])
                ->from(route('invoice.create', ['school_id' => $context['school']->id]))
                ->post(route('invoice.store'), $this->storePayload($context, array_merge([
                    'description' => 'Montant invalide',
                ], $amounts)))
                ->assertRedirect()
                ->assertSessionHasErrors('amount_ht');

            $this->assertDatabaseCount('invoices', 0);
        }
    }

    public function test_agenda_invoice_still_stores_planning_ttc_and_ht_lines(): void
    {
        $context = $this->makeAgendaContext();

        $this->actingAs($context['admin'])
            ->post(route('invoice.store'), $this->storePayload($context, [
                'description' => $context['course']->name,
                'course_id' => $context['course']->id,
            ]))
            ->assertRedirect(route('treasury.invoices.index'));

        $invoice = Invoice::query()->first();
        $this->assertEqualsWithDelta(240.0, (float) $invoice->amount, 0.02);

        $lines = app(InvoiceService::class)->resolveLines($invoice);
        $this->assertSame($context['course']->name, $lines[0][0]);
        $this->assertEqualsWithDelta(100.0, (float) $lines[0][2], 0.001);
        $this->assertEqualsWithDelta(2.0, (float) $lines[0][3], 0.001);
    }

    /**
     * @return array{admin: User, company: Company, school: School}
     */
    private function makeOnDemandContext(): array
    {
        $company = Company::factory()->create([
            'bill_prefix' => 'XDM',
            'name' => 'XDM Test',
            'address' => '1 rue Test',
            'city' => 'Paris',
            'zip' => '75001',
            'country' => 'France',
        ]);

        $admin = User::factory()->create([
            'company_id' => $company->id,
            'status_id' => Status::ADMIN,
            'mode' => 'Edit',
            'password' => 'password',
        ]);

        $school = School::query()->create([
            'name' => 'Client hors agenda',
            'code' => 'HOA1',
            'company_id' => $company->id,
            'address' => '2 avenue Client',
            'city' => 'Lyon',
            'zip' => '69001',
            'country' => 'France',
        ]);

        return [
            'admin' => $admin,
            'company' => $company,
            'school' => $school,
        ];
    }

    /**
     * @return array{admin: User, company: Company, school: School, course: Course, planning: Planning}
     */
    private function makeAgendaContext(): array
    {
        $context = $this->makeOnDemandContext();

        $program = Program::factory()->create(['company_id' => $context['company']->id]);
        $course = Course::factory()->create([
            'school_id' => $context['school']->id,
            'program_id' => $program->id,
            'name' => 'Alpha Course',
            'short_name' => 'ALP',
            'rate' => 100,
            'year' => 2026,
        ]);
        $group = Group::query()->create([
            'name' => 'Group Alpha',
            'short_name' => 'GA',
            'company_id' => $context['company']->id,
            'size' => 10,
            'active' => true,
            'year' => 2026,
        ]);
        $planning = Planning::create([
            'begin' => '2026-04-10 10:00:00',
            'end' => '2026-04-10 12:00:00',
            'location' => 'na',
            'group_id' => $group->id,
            'course_id' => $course->id,
        ]);

        return $context + [
            'course' => $course,
            'planning' => $planning,
        ];
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function storePayload(array $context, array $overrides = []): array
    {
        return array_merge([
            'invoice_id' => '26001',
            'description' => 'Facture test',
            'month' => 4,
            'year' => 2026,
            'bill_date' => '15/04/2026',
            'school_id' => $context['school']->id,
        ], $overrides);
    }

    private function assertPdfLineIsHtNotTtc(Invoice $invoice, string $designation, string $ht, string $ttc): void
    {
        $fresh = $invoice->fresh()->load(['company', 'school']);

        $pdf = new class($fresh) extends InvoiceGenerator
        {
            public string $expectedDesignation = '';

            /** @var list<string> */
            public array $cellsAfterDesignation = [];

            public bool $seenDesignation = false;

            public function Cell($w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = '', $fill = false, $link = '', $stretch = 0, $ignore_min_height = false, $calign = 'T', $valign = 'M')
            {
                if ($txt === $this->expectedDesignation) {
                    $this->seenDesignation = true;
                } elseif ($this->seenDesignation && is_string($txt) && $txt !== '') {
                    $this->cellsAfterDesignation[] = $txt;
                }

                return parent::Cell($w, $h, $txt, $border, $ln, $align, $fill, $link, $stretch, $ignore_min_height, $calign, $valign);
            }
        };
        $pdf->expectedDesignation = $designation;
        $pdf->setCompression(false);
        $pdf->AddPage();
        $pdf->drawInvoiceContent($fresh->company, $fresh->school, app(InvoiceService::class)->resolveLines($fresh));

        $this->assertTrue($pdf->seenDesignation);
        $this->assertGreaterThanOrEqual(3, count($pdf->cellsAfterDesignation));
        $this->assertSame('20%', $pdf->cellsAfterDesignation[0]);
        $this->assertSame($ht, $pdf->cellsAfterDesignation[1], 'P.U. HT must show HT, not the TTC figure');
        $this->assertSame('1.00', $pdf->cellsAfterDesignation[2]);
        $this->assertSame($ht, $pdf->cellsAfterDesignation[3], 'Line total must be HT');
        $this->assertContains($ttc, $pdf->cellsAfterDesignation);

        $binary = $pdf->Output('', 'S');
        $this->assertStringContainsString($designation, $binary);
        $this->assertStringContainsString($ht, $binary);
        $this->assertStringContainsString($ttc, $binary);
        $this->assertStringContainsString('Total TTC', $binary);
        $this->assertStringContainsString('Total HT', $binary);
        $this->assertStringContainsString('P.U. HT', $binary);
    }
}
