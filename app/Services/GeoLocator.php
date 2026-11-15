<?php

namespace App\Services;

use App\Models\LoginEvent;
use GeoIp2\Database\Reader;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class GeoLocator
{
    private ?Reader $reader = null;

    private bool $readerResolved = false;

    public function labelFor(string $ip): ?string
    {
        $ip = trim($ip);

        if ($ip === '' || $this->isUnusableIp($ip)) {
            return $this->isPrivateOrReserved($ip) ? LoginEvent::GEO_LOCAL : null;
        }

        if ($this->isPrivateOrReserved($ip)) {
            return LoginEvent::GEO_LOCAL;
        }

        $cacheKey = 'login-stats-geo:'.$ip;
        $cached = Cache::get($cacheKey);

        if ($cached !== null) {
            return $cached === '' ? null : $cached;
        }

        $label = $this->lookup($ip);
        $ttl = $label === null
            ? (int) config('login_stats.geo.failure_cache_ttl', 3600)
            : (int) config('login_stats.geo.cache_ttl', 2592000);

        Cache::put($cacheKey, $label ?? '', $ttl);

        return $label;
    }

    /**
     * Country name (or ISO code) from the local GeoLite2 MMDB only.
     * Never calls a third-party HTTP API — used by landing traffic.
     */
    public function countryFor(string $ip): ?string
    {
        $ip = trim($ip);

        if ($ip === '' || $this->isUnusableIp($ip) || $this->isPrivateOrReserved($ip)) {
            return null;
        }

        $cacheKey = 'landing-traffic-country:'.$ip;
        $cached = Cache::get($cacheKey);

        if ($cached !== null) {
            return $cached === '' ? null : $cached;
        }

        $country = $this->lookupCountryMmdb($ip);
        $ttl = $country === null
            ? (int) config('login_stats.geo.failure_cache_ttl', 3600)
            : (int) config('login_stats.geo.cache_ttl', 2592000);

        Cache::put($cacheKey, $country ?? '', $ttl);

        return $country;
    }

    private function lookup(string $ip): ?string
    {
        $fromMmdb = $this->lookupMmdb($ip);

        if ($fromMmdb !== null) {
            return $fromMmdb;
        }

        if (! config('login_stats.geo.http_enabled', true)) {
            return null;
        }

        return $this->lookupHttp($ip);
    }

    private function lookupCountryMmdb(string $ip): ?string
    {
        $reader = $this->reader();

        if ($reader === null) {
            return null;
        }

        try {
            $record = $reader->city($ip);
            $name = $record->country->name;
            $code = $record->country->isoCode;

            if (is_string($name) && $name !== '') {
                return $name;
            }

            return is_string($code) && $code !== '' ? $code : null;
        } catch (Throwable $e) {
            Log::debug('landing_traffic.geo_mmdb_miss', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function lookupMmdb(string $ip): ?string
    {
        $reader = $this->reader();

        if ($reader === null) {
            return null;
        }

        try {
            $record = $reader->city($ip);
            $parts = array_filter([
                $record->city->name,
                $record->mostSpecificSubdivision->name,
                $record->country->name,
            ]);

            return $parts === [] ? null : implode(', ', $parts);
        } catch (Throwable $e) {
            Log::debug('login_stats.geo_mmdb_miss', [
                'ip' => $ip,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function lookupHttp(string $ip): ?string
    {
        $template = (string) config('login_stats.geo.http_url', 'https://ipwho.is/{ip}');
        $url = str_replace('{ip}', rawurlencode($ip), $template);
        $timeout = (float) config('login_stats.geo.http_timeout', 1.5);

        try {
            $response = Http::timeout($timeout)
                ->connectTimeout(min($timeout, 1.0))
                ->acceptJson()
                ->get($url);

            if (! $response->successful()) {
                return null;
            }

            return $this->labelFromPayload($response->json() ?? []);
        } catch (Throwable $e) {
            Log::debug('login_stats.geo_http_failed', [
                'ip' => $ip,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * @param  array<string, mixed>  $json
     */
    private function labelFromPayload(array $json): ?string
    {
        $success = $json['success'] ?? $json['status'] ?? null;

        if ($success === false || $success === 'fail') {
            return null;
        }

        $parts = array_filter([
            $json['city'] ?? null,
            $json['region'] ?? $json['regionName'] ?? null,
            $json['country'] ?? null,
        ], fn ($value) => is_string($value) && $value !== '');

        return $parts === [] ? null : implode(', ', $parts);
    }

    private function reader(): ?Reader
    {
        if ($this->readerResolved) {
            return $this->reader;
        }

        $this->readerResolved = true;
        $path = (string) config('login_stats.geo.mmdb_path');

        if ($path === '' || ! is_readable($path) || ! class_exists(Reader::class)) {
            return null;
        }

        try {
            $this->reader = new Reader($path);
        } catch (Throwable $e) {
            Log::warning('login_stats.geo_mmdb_unreadable', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
            $this->reader = null;
        }

        return $this->reader;
    }

    private function isUnusableIp(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP) === false;
    }

    private function isPrivateOrReserved(string $ip): bool
    {
        if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
            return false;
        }

        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) === false;
    }
}
