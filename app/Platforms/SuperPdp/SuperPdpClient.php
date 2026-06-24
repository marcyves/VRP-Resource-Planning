<?php

namespace App\Platforms\SuperPdp;

use App\Exceptions\ElectronicInvoiceException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class SuperPdpClient
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly SuperPdpAuth $auth,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function sendInvoice(string $content, string $contentType, string $externalId): array
    {
        try {
            $response = Http::baseUrl(rtrim($this->baseUrl, '/'))
                ->withToken($this->auth->accessToken())
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => $contentType,
                ])
                ->withQueryParameters([
                    'external_id' => $externalId,
                ])
                ->withBody($content, $contentType)
                ->timeout(60)
                ->post('/v1.beta/invoices');

            $response->throw();

            /** @var array<string, mixed> $body */
            $body = $response->json() ?? [];

            return $body;
        } catch (RequestException $e) {
            throw $this->toElectronicInvoiceException($e);
        }
    }

    public function sendInvoicePdf(string $pdfContent, string $externalId): array
    {
        return $this->sendInvoice($pdfContent, 'application/pdf', $externalId);
    }

    public function convertInvoice(string $content, string $from, string $to): string
    {
        try {
            $response = Http::baseUrl(rtrim($this->baseUrl, '/'))
                ->withToken($this->auth->accessToken())
                ->withHeaders([
                    'Accept' => $this->convertAcceptHeader($to),
                    'Content-Type' => $this->convertContentTypeHeader($from),
                ])
                ->withQueryParameters([
                    'from' => $from,
                    'to' => $to,
                ])
                ->withBody($content, $this->convertContentTypeHeader($from))
                ->timeout(60)
                ->post('/v1.beta/invoices/convert');

            $response->throw();

            return $response->body();
        } catch (RequestException $e) {
            throw $this->toElectronicInvoiceException($e);
        }
    }

    public function generateTestInvoice(string $format = 'factur-x'): string
    {
        $response = Http::baseUrl(rtrim($this->baseUrl, '/'))
            ->withToken($this->auth->accessToken())
            ->withHeaders(['Accept' => 'application/pdf'])
            ->timeout(60)
            ->get('/v1.beta/invoices/generate_test_invoice', [
                'format' => $format,
            ]);

        $response->throw();

        return $response->body();
    }

    /**
     * @return array<string, mixed>
     */
    public function showInvoice(int $id): array
    {
        $response = Http::baseUrl(rtrim($this->baseUrl, '/'))
            ->withToken($this->auth->accessToken())
            ->acceptJson()
            ->timeout(30)
            ->get("/v1.beta/invoices/{$id}");

        $response->throw();

        /** @var array<string, mixed> */
        return $response->json() ?? [];
    }

    /**
     * @return array<string, mixed>
     */
    public function companyMe(): array
    {
        $response = Http::baseUrl(rtrim($this->baseUrl, '/'))
            ->withToken($this->auth->accessToken())
            ->acceptJson()
            ->timeout(30)
            ->get('/v1.beta/companies/me');

        $response->throw();

        /** @var array<string, mixed> */
        return $response->json() ?? [];
    }

    private function convertContentTypeHeader(string $from): string
    {
        return match ($from) {
            'factur-x' => 'application/pdf',
            default => 'application/xml',
        };
    }

    private function convertAcceptHeader(string $to): string
    {
        return match ($to) {
            'factur-x' => 'application/pdf',
            default => 'application/xml',
        };
    }

    private function toElectronicInvoiceException(RequestException $e): ElectronicInvoiceException
    {
        $response = $e->response;
        $message = $response?->json('message')
            ?? $response?->json('error')
            ?? $e->getMessage();

        return new ElectronicInvoiceException(
            __('messages.electronic_invoice_superpdp_error', ['message' => $message]),
            $this->extractValidationErrors($response?->json()),
        );
    }

    /**
     * @param  array<string, mixed>|null  $payload
     * @return list<string>
     */
    private function extractValidationErrors(?array $payload): array
    {
        if ($payload === null) {
            return [];
        }

        $errors = [];

        foreach (['failures', 'errors'] as $key) {
            if (! isset($payload[$key]) || ! is_array($payload[$key])) {
                continue;
            }

            foreach ($payload[$key] as $failure) {
                if (! is_array($failure)) {
                    continue;
                }

                $text = trim((string) ($failure['message'] ?? ''));
                if ($text !== '') {
                    $location = trim((string) ($failure['location'] ?? ''));
                    $errors[] = $location !== '' ? "{$location} — {$text}" : $text;
                }
            }
        }

        $report = $payload['report'] ?? $payload['validation_report'] ?? null;
        if (is_array($report)) {
            foreach ($report['failures'] ?? [] as $failure) {
                if (! is_array($failure)) {
                    continue;
                }

                $text = trim((string) ($failure['message'] ?? ''));
                if ($text !== '') {
                    $errors[] = $text;
                }
            }
        }

        if ($errors === [] && isset($payload['data']) && is_array($payload['data'])) {
            foreach ($payload['data'] as $item) {
                if (! is_array($item)) {
                    continue;
                }

                foreach ($item['failures'] ?? [] as $failure) {
                    if (! is_array($failure)) {
                        continue;
                    }

                    $text = trim((string) ($failure['message'] ?? ''));
                    if ($text !== '') {
                        $errors[] = $text;
                    }
                }
            }
        }

        return array_values(array_unique($errors));
    }
}
