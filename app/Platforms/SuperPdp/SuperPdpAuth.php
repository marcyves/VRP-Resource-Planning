<?php

namespace App\Platforms\SuperPdp;

use App\Exceptions\ElectronicInvoiceException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class SuperPdpAuth
{
    private const CACHE_KEY = 'superpdp.access_token';

    public function __construct(
        private readonly string $baseUrl,
        private readonly ?string $clientId,
        private readonly ?string $clientSecret,
        private readonly ?string $accessToken = null,
    ) {}

    public function isConfigured(): bool
    {
        return filled($this->accessToken)
            || (filled($this->clientId) && filled($this->clientSecret));
    }

    public function accessToken(): string
    {
        if (filled($this->accessToken)) {
            return $this->accessToken;
        }

        if (! filled($this->clientId) || ! filled($this->clientSecret)) {
            throw new ElectronicInvoiceException(__('messages.electronic_invoice_platform_not_configured'));
        }

        $cacheKey = self::CACHE_KEY.'.'.md5($this->clientId);

        /** @var string|null $cached */
        $cached = Cache::get($cacheKey);
        if ($cached) {
            return $cached;
        }

        try {
            $response = Http::baseUrl(rtrim($this->baseUrl, '/'))
                ->asForm()
                ->timeout(30)
                ->post('/oauth2/token', [
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                ]);

            $response->throw();
        } catch (RequestException $e) {
            $message = $e->response?->json('error_description')
                ?? $e->response?->json('message')
                ?? $e->response?->json('error')
                ?? $e->getMessage();

            throw new ElectronicInvoiceException(
                __('messages.electronic_invoice_superpdp_auth_error', ['message' => $message]),
            );
        }

        /** @var array<string, mixed> $body */
        $body = $response->json() ?? [];
        $token = $body['access_token'] ?? null;

        if (! is_string($token) || $token === '') {
            throw new ElectronicInvoiceException(__('messages.electronic_invoice_superpdp_auth_error', [
                'message' => 'missing access_token',
            ]));
        }

        $expiresIn = is_numeric($body['expires_in'] ?? null) ? (int) $body['expires_in'] : 3600;
        $ttl = max(60, $expiresIn - 60);

        Cache::put($cacheKey, $token, $ttl);

        return $token;
    }
}
