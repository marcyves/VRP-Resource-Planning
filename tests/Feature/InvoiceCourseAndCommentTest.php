<?php

namespace Tests\Feature;

use App\Classes\InvoiceGenerator;
use App\Models\Company;
use App\Models\Course;
use App\Models\Group;
use App\Models\Invoice;
use App\Models\Planning;
use App\Models\Program;
use App\Models\School;
use App\Models\Status;
use App\Models\User;
use App\Services\ElectronicInvoicing\ElectronicInvoiceCiiBuilder;
use App\Services\InvoiceService;
use Database\Seeders\StatusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InvoiceCourseAndCommentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(StatusSeeder::class);
        Storage::fake('local');
    }

    public function test_monthly_invoice_links_all_courses_of_the_month(): void
    {
        $context = $this->makeBillingContext();

        $this->actingAs($context['admin'])
            ->post(route('invoice.store'), $this->storePayload($context, [
                'description' => 'Mois complet',
            ]))
            ->assertRedirect(route('treasury.invoices.index'));

        $invoice = Invoice::query()->first();
        $this->assertNotNull($invoice);
        $this->assertNull($invoice->course_id);
        $this->assertSame(
            $context['company']->bill_prefix.$invoice->id,
            $context['planningAlpha']->fresh()->invoice_id
        );
        $this->assertSame(
            $context['company']->bill_prefix.$invoice->id,
            $context['planningBeta']->fresh()->invoice_id
        );
        $this->assertEqualsWithDelta(432.0, (float) $invoice->amount, 0.02);
    }

    public function test_course_scoped_invoice_only_links_that_course_sessions(): void
    {
        $context = $this->makeBillingContext();

        $html = $this->actingAs($context['admin'])
            ->get(route('invoice.create', [
                'school_id' => $context['school']->id,
                'course_id' => $context['courseAlpha']->id,
                'month' => 3,
                'year' => 2026,
                'cmd' => 'detailed',
                'from_school_billing' => 1,
            ]))
            ->assertOk()
            ->assertSee($context['courseAlpha']->name, false)
            ->assertSee(__('messages.invoice_scope'), false)
            ->getContent();

        $this->assertStringContainsString('invoice-create-items', $html);
        $this->assertStringContainsString($context['courseAlpha']->name, $html);
        $this->assertStringNotContainsString($context['courseBeta']->name, $html);

        $this->actingAs($context['admin'])
            ->post(route('invoice.store'), $this->storePayload($context, [
                'description' => $context['courseAlpha']->name,
                'course_id' => $context['courseAlpha']->id,
            ]))
            ->assertRedirect(route('treasury.invoices.index'));

        $invoice = Invoice::query()->first();
        $this->assertNotNull($invoice);
        $this->assertSame($context['courseAlpha']->id, (int) $invoice->course_id);
        $this->assertSame(
            $context['company']->bill_prefix.$invoice->id,
            $context['planningAlpha']->fresh()->invoice_id
        );
        $this->assertTrue(in_array($context['planningBeta']->fresh()->invoice_id, [null, ''], true));
        $this->assertEqualsWithDelta(240.0, (float) $invoice->amount, 0.02);
    }

    public function test_invoice_comment_is_saved_and_displayed(): void
    {
        $context = $this->makeBillingContext();
        $comment = 'Mention PO-42 pour le client';

        $this->actingAs($context['admin'])
            ->post(route('invoice.store'), $this->storePayload($context, [
                'description' => 'Avec commentaire',
                'comment' => $comment,
                'course_id' => $context['courseAlpha']->id,
            ]))
            ->assertRedirect(route('treasury.invoices.index'));

        $invoice = Invoice::query()->first();
        $this->assertSame($comment, $invoice->comment);

        $this->actingAs($context['admin'])
            ->get(route('treasury.invoices.index'))
            ->assertOk()
            ->assertSee($comment, false);

        $this->actingAs($context['admin'])
            ->get(route('invoice.edit', $invoice))
            ->assertOk()
            ->assertSee($comment, false)
            ->assertSee(__('messages.invoice_comment'), false);

        $updated = 'PO-42 updated comment';
        $this->actingAs($context['admin'])
            ->patch(route('invoice.update', $invoice), [
                'id' => $invoice->id,
                'description' => 'Avec commentaire',
                'comment' => $updated,
                'amount' => $invoice->amountHt(),
            ])
            ->assertRedirect(route('treasury.invoices.index'));

        $this->assertSame($updated, $invoice->fresh()->comment);

        $xml = (new ElectronicInvoiceCiiBuilder)->build($invoice->fresh()->load(['company', 'school']));
        $this->assertStringContainsString($updated, $xml);
        $this->assertStringContainsString('>AAI<', $xml);

        $fresh = $invoice->fresh()->load(['company', 'school']);
        $pdf = new InvoiceGenerator($fresh);
        $pdf->setCompression(false);
        $pdf->AddPage();
        $pdf->drawInvoiceContent($fresh->company, $fresh->school, app(InvoiceService::class)->resolveLines($fresh));
        $this->assertStringContainsString($updated, $pdf->Output('', 'S'));
    }

    public function test_pdf_comment_appears_in_designation_at_title_font_size(): void
    {
        $context = $this->makeBillingContext();
        $comment = 'Mention PO-42 pour le client';

        $this->actingAs($context['admin'])
            ->post(route('invoice.store'), $this->storePayload($context, [
                'description' => 'Avec commentaire',
                'comment' => $comment,
                'course_id' => $context['courseAlpha']->id,
            ]))
            ->assertRedirect(route('treasury.invoices.index'));

        $fresh = Invoice::query()->first()->load(['company', 'school']);
        $courseTitle = $context['courseAlpha']->name;

        $pdf = new class($fresh) extends InvoiceGenerator
        {
            public string $expectedTitle = '';

            public string $expectedComment = '';

            public ?float $titleY = null;

            public ?float $titleFontPt = null;

            public ?float $commentY = null;

            public ?float $commentFontPt = null;

            public ?float $commentWidth = null;

            public ?float $paymentTermsY = null;

            public int $commentDrawCount = 0;

            public function Cell($w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = '', $fill = false, $link = '', $stretch = 0, $ignore_min_height = false, $calign = 'T', $valign = 'M')
            {
                if ($this->titleY === null && $txt === $this->expectedTitle) {
                    $this->titleY = $this->GetY();
                    $this->titleFontPt = $this->GetFontSizePt();
                }
                if (is_string($txt) && str_contains($txt, 'Conditions de règlement')) {
                    $this->paymentTermsY = $this->GetY();
                }

                return parent::Cell($w, $h, $txt, $border, $ln, $align, $fill, $link, $stretch, $ignore_min_height, $calign, $valign);
            }

            public function MultiCell($w, $h, $txt, $border = 0, $align = 'J', $fill = false, $ln = 1, $x = null, $y = null, $reseth = true, $stretch = 0, $ishtml = false, $autopadding = true, $maxh = 0, $valign = 'T', $fitcell = false)
            {
                if ($txt === $this->expectedComment) {
                    $this->commentDrawCount++;
                    $this->commentY = $this->GetY();
                    $this->commentFontPt = $this->GetFontSizePt();
                    $this->commentWidth = $w;
                }

                return parent::MultiCell($w, $h, $txt, $border, $align, $fill, $ln, $x, $y, $reseth, $stretch, $ishtml, $autopadding, $maxh, $valign, $fitcell);
            }
        };
        $pdf->expectedTitle = $courseTitle;
        $pdf->expectedComment = $comment;
        $pdf->setCompression(false);
        $pdf->AddPage();
        $pdf->drawInvoiceContent($fresh->company, $fresh->school, app(InvoiceService::class)->resolveLines($fresh));

        $this->assertSame(1, $pdf->commentDrawCount);
        $this->assertNotNull($pdf->titleY);
        $this->assertNotNull($pdf->commentY);
        $this->assertNotNull($pdf->paymentTermsY);
        $this->assertGreaterThan($pdf->titleY, $pdf->commentY);
        $this->assertLessThan($pdf->paymentTermsY, $pdf->commentY);
        $this->assertSame($pdf->titleFontPt, $pdf->commentFontPt);
        $this->assertEqualsWithDelta(12.0, (float) $pdf->commentFontPt, 0.01);
        $this->assertEqualsWithDelta(108.0, (float) $pdf->commentWidth, 0.01);
        $this->assertStringContainsString($comment, $pdf->Output('', 'S'));
        $this->assertStringContainsString($courseTitle, $pdf->Output('', 'S'));
    }

    public function test_pdf_comment_is_drawn_once_on_monthly_invoice(): void
    {
        $context = $this->makeBillingContext();
        $comment = 'Mention PO-42 pour le client';

        $this->actingAs($context['admin'])
            ->post(route('invoice.store'), $this->storePayload($context, [
                'description' => 'Mois complet',
                'comment' => $comment,
            ]))
            ->assertRedirect(route('treasury.invoices.index'));

        $fresh = Invoice::query()->first()->load(['company', 'school']);

        $pdf = new class($fresh) extends InvoiceGenerator
        {
            public int $commentDrawCount = 0;

            public function MultiCell($w, $h, $txt, $border = 0, $align = 'J', $fill = false, $ln = 1, $x = null, $y = null, $reseth = true, $stretch = 0, $ishtml = false, $autopadding = true, $maxh = 0, $valign = 'T', $fitcell = false)
            {
                if ($txt === 'Mention PO-42 pour le client') {
                    $this->commentDrawCount++;
                }

                return parent::MultiCell($w, $h, $txt, $border, $align, $fill, $ln, $x, $y, $reseth, $stretch, $ishtml, $autopadding, $maxh, $valign, $fitcell);
            }
        };
        $pdf->setCompression(false);
        $pdf->AddPage();
        $pdf->drawInvoiceContent($fresh->company, $fresh->school, app(InvoiceService::class)->resolveLines($fresh));

        $this->assertSame(1, $pdf->commentDrawCount);
        $this->assertStringContainsString($comment, $pdf->Output('', 'S'));
        $this->assertStringContainsString($context['courseAlpha']->name, $pdf->Output('', 'S'));
        $this->assertStringContainsString($context['courseBeta']->name, $pdf->Output('', 'S'));
    }

    public function test_reader_cannot_create_or_store_invoice(): void
    {
        $context = $this->makeBillingContext();

        $this->actingAs($context['reader'])
            ->get(route('invoice.create', ['school_id' => $context['school']->id]))
            ->assertForbidden();

        $this->actingAs($context['reader'])
            ->post(route('invoice.store'), $this->storePayload($context, [
                'description' => 'Interdit',
            ]))
            ->assertForbidden();

        $this->assertDatabaseCount('invoices', 0);
    }

    public function test_school_billing_offers_course_and_month_invoice_actions(): void
    {
        $context = $this->makeBillingContext();

        $response = $this->actingAs($context['admin'])
            ->withSession([
                'billing_year' => 2026,
                'current_month' => 3,
                'current_year' => 2026,
            ])
            ->get(route('school.show', $context['school']));

        $response->assertOk()
            ->assertSee('<h4 class="billing-course-title">'.$context['courseAlpha']->name.'</h4>', false)
            ->assertSee('<h4 class="billing-course-title">'.$context['courseBeta']->name.'</h4>', false)
            ->assertSee(__('messages.invoice_create_this_course'), false)
            ->assertSee(__('messages.invoice_create_all_month'), false)
            ->assertDontSee(__('messages.invoice_create_this_course').' '.$context['courseAlpha']->name, false)
            ->assertDontSee(__('messages.invoice_create_this_course').' '.$context['courseBeta']->name, false);
    }

    /**
     * @return array{
     *     admin: User,
     *     reader: User,
     *     company: Company,
     *     school: School,
     *     courseAlpha: Course,
     *     courseBeta: Course,
     *     planningAlpha: Planning,
     *     planningBeta: Planning
     * }
     */
    private function makeBillingContext(): array
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

        $reader = User::factory()->create([
            'company_id' => $company->id,
            'status_id' => Status::READER,
            'mode' => 'Browse',
            'password' => 'password',
        ]);

        $school = School::query()->create([
            'name' => 'Client test',
            'code' => 'CLT1',
            'company_id' => $company->id,
            'address' => '2 avenue Client',
            'city' => 'Lyon',
            'zip' => '69001',
            'country' => 'France',
        ]);

        $program = Program::factory()->create(['company_id' => $company->id]);

        $courseAlpha = Course::factory()->create([
            'school_id' => $school->id,
            'program_id' => $program->id,
            'name' => 'Alpha Course',
            'short_name' => 'ALP',
            'rate' => 100,
            'year' => 2026,
        ]);
        $courseBeta = Course::factory()->create([
            'school_id' => $school->id,
            'program_id' => $program->id,
            'name' => 'Beta Course',
            'short_name' => 'BET',
            'rate' => 80,
            'year' => 2026,
        ]);

        $groupAlpha = Group::query()->create([
            'name' => 'Group Alpha',
            'short_name' => 'GA',
            'company_id' => $company->id,
            'size' => 10,
            'active' => true,
            'year' => 2026,
        ]);
        $groupBeta = Group::query()->create([
            'name' => 'Group Beta',
            'short_name' => 'GB',
            'company_id' => $company->id,
            'size' => 10,
            'active' => true,
            'year' => 2026,
        ]);

        $planningAlpha = Planning::create([
            'begin' => '2026-03-10 10:00:00',
            'end' => '2026-03-10 12:00:00',
            'location' => 'na',
            'group_id' => $groupAlpha->id,
            'course_id' => $courseAlpha->id,
        ]);
        $planningBeta = Planning::create([
            'begin' => '2026-03-11 10:00:00',
            'end' => '2026-03-11 12:00:00',
            'location' => 'na',
            'group_id' => $groupBeta->id,
            'course_id' => $courseBeta->id,
        ]);

        return [
            'admin' => $admin,
            'reader' => $reader,
            'company' => $company,
            'school' => $school,
            'courseAlpha' => $courseAlpha,
            'courseBeta' => $courseBeta,
            'planningAlpha' => $planningAlpha,
            'planningBeta' => $planningBeta,
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
            'month' => 3,
            'year' => 2026,
            'bill_date' => '15/03/2026',
            'school_id' => $context['school']->id,
        ], $overrides);
    }
}
