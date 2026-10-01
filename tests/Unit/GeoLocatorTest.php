<?php

namespace Tests\Unit;

use App\Models\LoginEvent;
use App\Services\GeoLocator;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GeoLocatorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        config([
            'login_stats.geo.http_enabled' => true,
            'login_stats.geo.mmdb_path' => storage_path('app/geoip/missing.mmdb'),
            'login_stats.geo.http_url' => 'https://ipwho.is/{ip}',
            'login_stats.geo.http_timeout' => 1.5,
        ]);
    }

    public function test_private_ip_is_local_without_http(): void
    {
        Http::fake();

        $label = app(GeoLocator::class)->labelFor('127.0.0.1');

        $this->assertSame(LoginEvent::GEO_LOCAL, $label);
        Http::assertNothingSent();
    }

    public function test_http_lookup_is_cached(): void
    {
        Http::fake([
            'ipwho.is/*' => Http::response([
                'success' => true,
                'city' => 'Paris',
                'region' => 'Île-de-France',
                'country' => 'France',
            ]),
        ]);

        $locator = app(GeoLocator::class);

        $this->assertSame('Paris, Île-de-France, France', $locator->labelFor('8.8.8.8'));
        $this->assertSame('Paris, Île-de-France, France', $locator->labelFor('8.8.8.8'));
        Http::assertSentCount(1);
    }

    public function test_http_failure_returns_null(): void
    {
        Http::fake(function () {
            throw new ConnectionException('timeout');
        });

        $this->assertNull(app(GeoLocator::class)->labelFor('8.8.8.8'));
    }
}
