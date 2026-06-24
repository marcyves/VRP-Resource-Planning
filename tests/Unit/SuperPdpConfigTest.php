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
}
