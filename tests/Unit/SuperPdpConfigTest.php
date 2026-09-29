<?php

namespace Tests\Unit;

use App\Platforms\SuperPdp\SuperPdpConfig;
use Tests\TestCase;

class SuperPdpConfigTest extends TestCase
{
    public function test_uses_sandbox_credentials_when_env_is_sandbox(): void
    {
        [$clientId, $clientSecret, $env] = SuperPdpConfig::activeCredentials([
            'env' => 'sandbox',
            'client_id' => 'prod-id',
            'client_secret' => 'prod-secret',
            'sandbox_client_id' => 'sandbox-id',
            'sandbox_client_secret' => 'sandbox-secret',
        ]);

        $this->assertSame('sandbox-id', $clientId);
        $this->assertSame('sandbox-secret', $clientSecret);
        $this->assertSame('sandbox', $env);
    }

    public function test_uses_production_credentials_when_env_is_production(): void
    {
        [$clientId, $clientSecret, $env] = SuperPdpConfig::activeCredentials([
            'env' => 'production',
            'client_id' => 'prod-id',
            'client_secret' => 'prod-secret',
            'sandbox_client_id' => 'sandbox-id',
            'sandbox_client_secret' => 'sandbox-secret',
        ]);

        $this->assertSame('prod-id', $clientId);
        $this->assertSame('prod-secret', $clientSecret);
        $this->assertSame('production', $env);
    }

    public function test_defaults_to_sandbox_and_blocks_live_pa(): void
    {
        $this->assertSame('sandbox', SuperPdpConfig::environment());
        $this->assertFalse(SuperPdpConfig::allowProduction());
        $this->assertFalse(SuperPdpConfig::isProductionBlocked());
        $this->assertTrue(SuperPdpConfig::outboundAllowed());
    }

    public function test_production_env_is_blocked_without_explicit_allow_flag(): void
    {
        config([
            'electronic-invoicing.platform' => 'superpdp',
            'electronic-invoicing.allow_production' => false,
            'electronic-invoicing.superpdp.env' => 'production',
        ]);

        $this->assertTrue(SuperPdpConfig::isProductionBlocked());
        $this->assertFalse(SuperPdpConfig::outboundAllowed());
    }

    public function test_production_is_allowed_only_with_explicit_flag(): void
    {
        config([
            'electronic-invoicing.platform' => 'superpdp',
            'electronic-invoicing.allow_production' => true,
            'electronic-invoicing.superpdp.env' => 'production',
        ]);

        $this->assertFalse(SuperPdpConfig::isProductionBlocked());
        $this->assertTrue(SuperPdpConfig::outboundAllowed());
    }
}
