<?php

namespace Tests\Unit;

use App\Enums\PlatformEventType;
use App\Exceptions\ElectronicInvoiceException;
use App\Models\Invoice;
use App\Platforms\SuperPdp\SuperPdpClient;
use App\Platforms\SuperPdp\SuperPdpPlatform;
use App\Services\ElectronicInvoicing\ElectronicInvoiceCiiBuilder;
use App\Services\ElectronicInvoicing\ElectronicInvoiceValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;

class SuperPdpPlatformTest extends TestCase
{
    use RefreshDatabase;

    public function test_is_not_configured_when_live_pa_is_locked(): void
    {
        config([
            'electronic-invoicing.allow_production' => false,
            'electronic-invoicing.superpdp.env' => 'production',
        ]);

        $platform = new SuperPdpPlatform(
            Mockery::mock(SuperPdpClient::class),
            new ElectronicInvoiceValidator,
            new ElectronicInvoiceCiiBuilder,
            'secret',
        );

        $this->assertFalse($platform->isConfigured());
        $this->expectException(ElectronicInvoiceException::class);
        $platform->submitOutbound(new Invoice);
    }

    public function test_is_configured_in_sandbox_when_client_exists(): void
    {
        config([
            'electronic-invoicing.allow_production' => false,
            'electronic-invoicing.superpdp.env' => 'sandbox',
        ]);

        $platform = new SuperPdpPlatform(
            Mockery::mock(SuperPdpClient::class),
            new ElectronicInvoiceValidator,
            new ElectronicInvoiceCiiBuilder,
            'secret',
        );

        $this->assertTrue($platform->isConfigured());
    }

    public function test_parse_webhook_maps_status_hints(): void
    {
        $platform = $this->platform('secret');

        $accepted = $platform->parseWebhook(Request::create('/', 'POST', [
            'id' => '42',
            'status' => 'accepted',
            'external_id' => 'XDM26001',
        ]));
        $this->assertSame(PlatformEventType::OutboundAccepted, $accepted->type);
        $this->assertSame('42', $accepted->pdpReference);
        $this->assertSame('XDM26001', $accepted->vrpInvoiceId);

        $rejected = $platform->parseWebhook(Request::create('/', 'POST', [
            'invoice_id' => '99',
            'status_code' => 'rejected',
            'status_message' => 'SIREN',
        ]));
        $this->assertSame(PlatformEventType::OutboundRejected, $rejected->type);
        $this->assertSame('SIREN', $rejected->rejectionReason);
    }

    public function test_verify_webhook_requires_matching_hmac(): void
    {
        $platform = $this->platform('top-secret');
        $payload = '{"id":"42"}';
        $request = Request::create('/', 'POST', [], [], [], [
            'HTTP_X_SUPERPDP_SIGNATURE' => hash_hmac('sha256', $payload, 'top-secret'),
            'CONTENT_TYPE' => 'application/json',
        ], $payload);

        $this->assertTrue($platform->verifyWebhook($request));

        $bad = Request::create('/', 'POST', [], [], [], [
            'HTTP_X_SUPERPDP_SIGNATURE' => 'nope',
            'CONTENT_TYPE' => 'application/json',
        ], $payload);
        $this->assertFalse($platform->verifyWebhook($bad));

        $noSecret = $this->platform(null);
        $this->assertFalse($noSecret->verifyWebhook($request));
    }

    public function test_verify_webhook_uses_env_secret_when_constructor_secret_is_empty(): void
    {
        config(['electronic-invoicing.superpdp.webhook_secret' => 'from-env']);

        $platform = $this->platform(null);
        $payload = '{"id":"42"}';
        $request = Request::create('/', 'POST', [], [], [], [
            'HTTP_X_SUPERPDP_SIGNATURE' => hash_hmac('sha256', $payload, 'from-env'),
            'CONTENT_TYPE' => 'application/json',
        ], $payload);

        $this->assertTrue($platform->verifyWebhook($request));
    }

    private function platform(?string $secret): SuperPdpPlatform
    {
        return new SuperPdpPlatform(
            null,
            new ElectronicInvoiceValidator,
            new ElectronicInvoiceCiiBuilder,
            $secret,
        );
    }
}
