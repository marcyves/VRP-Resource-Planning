<?php

namespace Tests\Feature\SuperAdmin;

use App\Contracts\ElectronicInvoicePlatform;
use App\Models\Company;
use App\Models\PlatformSetting;
use App\Models\Status;
use App\Models\User;
use App\Platforms\SuperPdp\SuperPdpConfig;
use Database\Seeders\StatusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ElectronicInvoicingSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(StatusSeeder::class);
    }

    public function test_super_admin_can_store_encrypted_webhook_secret(): void
    {
        $superAdmin = $this->makeSuperAdmin();
        $secret = 'ui-hmac-secret-value';

        $this->actingAs($superAdmin)
            ->from(route('super-admin.electronic-invoicing.edit'))
            ->patch(route('super-admin.electronic-invoicing.update'), [
                'superpdp_webhook_secret' => $secret,
            ])
            ->assertRedirect(route('super-admin.electronic-invoicing.edit'))
            ->assertSessionHas('success')
            ->assertSessionMissing('errors');

        $raw = DB::table('platform_settings')->value('superpdp_webhook_secret');
        $this->assertIsString($raw);
        $this->assertNotSame($secret, $raw);
        $this->assertStringNotContainsString($secret, $raw);

        $this->assertSame($secret, SuperPdpConfig::storedWebhookSecret());
        $this->assertSame('ui', SuperPdpConfig::webhookSecretSource());
    }

    public function test_saved_secret_is_masked_and_never_redisplayed(): void
    {
        $superAdmin = $this->makeSuperAdmin();
        $secret = 'must-not-appear-in-html';

        $this->actingAs($superAdmin)
            ->patch(route('super-admin.electronic-invoicing.update'), [
                'superpdp_webhook_secret' => $secret,
            ]);

        $this->actingAs($superAdmin)
            ->get(route('super-admin.electronic-invoicing.edit'))
            ->assertOk()
            ->assertSee(__('messages.super_admin_webhook_secret_stored'), false)
            ->assertSee('••••••••', false)
            ->assertDontSee($secret, false)
            ->assertSee(SuperPdpConfig::publicWebhookUrl(), false);
    }

    public function test_empty_submit_keeps_existing_secret(): void
    {
        $settings = PlatformSetting::current();
        $settings->superpdp_webhook_secret = 'keep-this-secret';
        $settings->save();

        $this->actingAs($this->makeSuperAdmin())
            ->patch(route('super-admin.electronic-invoicing.update'), [
                'superpdp_webhook_secret' => '',
            ])
            ->assertRedirect(route('super-admin.electronic-invoicing.edit'));

        $this->assertSame('keep-this-secret', SuperPdpConfig::storedWebhookSecret());
    }

    public function test_clearing_stored_secret_falls_back_to_env(): void
    {
        config(['electronic-invoicing.superpdp.webhook_secret' => 'env-fallback-secret']);

        $settings = PlatformSetting::current();
        $settings->superpdp_webhook_secret = 'ui-wins-until-cleared';
        $settings->save();

        $this->assertSame('ui-wins-until-cleared', SuperPdpConfig::webhookSecret());

        $this->actingAs($this->makeSuperAdmin())
            ->patch(route('super-admin.electronic-invoicing.update'), [
                'clear_superpdp_webhook_secret' => '1',
            ])
            ->assertRedirect(route('super-admin.electronic-invoicing.edit'));

        $this->assertNull(SuperPdpConfig::storedWebhookSecret());
        $this->assertSame('env-fallback-secret', SuperPdpConfig::webhookSecret());
        $this->assertSame('env', SuperPdpConfig::webhookSecretSource());
    }

    public function test_runtime_prefers_ui_secret_over_env(): void
    {
        config(['electronic-invoicing.superpdp.webhook_secret' => 'env-secret-should-lose']);

        $settings = PlatformSetting::current();
        $settings->superpdp_webhook_secret = 'ui-secret-should-win';
        $settings->save();

        $this->assertSame('ui-secret-should-win', SuperPdpConfig::webhookSecret());
        $this->assertTrue(SuperPdpConfig::webhookSecretConfigured());
    }

    public function test_tenant_cannot_access_process_webhook_settings(): void
    {
        $tenantAdmin = User::factory()->create([
            'status_id' => Status::ADMIN,
            'company_id' => Company::factory()->create()->id,
            'password' => 'password',
        ]);

        $this->actingAs($tenantAdmin)
            ->get(route('super-admin.electronic-invoicing.edit'))
            ->assertForbidden();

        $this->actingAs($tenantAdmin)
            ->patch(route('super-admin.electronic-invoicing.update'), [
                'superpdp_webhook_secret' => 'stolen-secret-value',
            ])
            ->assertForbidden();

        $this->assertNull(SuperPdpConfig::storedWebhookSecret());
    }

    public function test_webhook_hmac_uses_ui_stored_secret(): void
    {
        config(['electronic-invoicing.platform' => 'superpdp']);
        $this->app->forgetInstance(ElectronicInvoicePlatform::class);

        $settings = PlatformSetting::current();
        $settings->superpdp_webhook_secret = 'stored-hmac-secret';
        $settings->save();

        $payload = json_encode(['id' => '42', 'status' => 'accepted'], JSON_THROW_ON_ERROR);

        $this->call(
            'POST',
            route('webhooks.e-invoice', 'superpdp'),
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_SUPERPDP_SIGNATURE' => hash_hmac('sha256', $payload, 'stored-hmac-secret'),
            ],
            $payload,
        )->assertNoContent();
    }

    public function test_go_live_check_reports_ui_secret_without_printing_it(): void
    {
        $settings = PlatformSetting::current();
        $settings->superpdp_webhook_secret = 'super-secret-ui-value';
        $settings->save();

        $this->artisan('superpdp:go-live-check')
            ->expectsOutputToContain('oui (interface super-admin)')
            ->doesntExpectOutputToContain('super-secret-ui-value')
            ->assertSuccessful();
    }

    private function makeSuperAdmin(): User
    {
        return User::factory()->create([
            'company_id' => null,
            'status_id' => Status::superAdminId(),
            'password' => 'password',
        ]);
    }
}
