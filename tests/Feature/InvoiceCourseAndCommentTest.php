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

        $this->actingAs($context['admin'])
            ->withSession([
                'billing_year' => 2026,
                'current_month' => 3,
                'current_year' => 2026,
            ])
            ->get(route('school.show', $context['school']))
            ->assertOk()
            ->assertSee(__('messages.invoice_create_this_course', ['name' => $context['courseAlpha']->name]), false)
            ->assertSee(__('messages.invoice_create_this_course', ['name' => $context['courseBeta']->name]), false)
            ->assertSee(__('messages.invoice_create_all_month'), false);
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
