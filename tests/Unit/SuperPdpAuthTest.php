<?php

namespace Tests\Unit;

use App\Platforms\SuperPdp\SuperPdpAuth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SuperPdpAuthTest extends TestCase
{
    public function test_client_credentials_are_exchanged_for_access_token(): void
    {
        Cache::flush();

        Http::fake([
            'api.superpdp.tech/oauth2/token' => Http::response([
                'access_token' => 'sandbox-token',
                'token_type' => 'Bearer',
                'expires_in' => 3600,
            ]),
        ]);

        $auth = new SuperPdpAuth(
            'https://api.superpdp.tech',
            'client-id',
            'client-secret',
        );

        $this->assertSame('sandbox-token', $auth->accessToken());
        $this->assertSame('sandbox-token', $auth->accessToken());

        Http::assertSentCount(1);
    }
}
