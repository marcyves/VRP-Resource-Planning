<?php

namespace App\Platforms\SuperPdp;

class SuperPdpConfig
{
    /**
     * @param  array<string, mixed>|null  $config
     * @return array<string, mixed>
     */
    public static function appConfig(?array $config = null): array
    {
        return $config ?? config('electronic-invoicing', []);
    }

    /**
     * @param  array<string, mixed>|null  $config
     * @return array<string, mixed>
     */
    public static function superpdpConfig(?array $config = null): array
    {
        $app = self::appConfig($config);

        if (isset($app['superpdp']) && is_array($app['superpdp'])) {
            return $app['superpdp'];
        }

        return $app;
    }

    /**
     * @param  array<string, mixed>  $config  SuperPDP subtree or full electronic-invoicing config
     * @return array{0: ?string, 1: ?string, 2: string}
     */
    public static function activeCredentials(array $config): array
    {
        $superpdp = isset($config['superpdp']) && is_array($config['superpdp'])
            ? $config['superpdp']
            : $config;

        $env = self::environment($superpdp);

        if ($env === 'sandbox') {
            return [
                $superpdp['sandbox_client_id'] ?? $superpdp['client_id'] ?? null,
                $superpdp['sandbox_client_secret'] ?? $superpdp['client_secret'] ?? null,
                'sandbox',
            ];
        }

        return [
            $superpdp['client_id'] ?? null,
            $superpdp['client_secret'] ?? null,
            'production',
        ];
    }

    /**
     * @param  array<string, mixed>|null  $config
     */
    public static function environment(?array $config = null): string
    {
        $superpdp = self::superpdpConfig($config);
        $env = strtolower((string) ($superpdp['env'] ?? 'sandbox'));

        return $env === 'production' ? 'production' : 'sandbox';
    }

    /**
     * @param  array<string, mixed>|null  $config
     */
    public static function allowProduction(?array $config = null): bool
    {
        $app = self::appConfig($config);

        if (array_key_exists('allow_production', $app)) {
            return (bool) $app['allow_production'];
        }

        return false;
    }

    /**
     * Live SuperPDP is requested but the explicit production lock is still off.
     *
     * @param  array<string, mixed>|null  $config
     */
    public static function isProductionBlocked(?array $config = null): bool
    {
        return self::environment($config) === 'production'
            && ! self::allowProduction($config);
    }

    /**
     * @param  array<string, mixed>|null  $config
     */
    public static function platform(?array $config = null): ?string
    {
        $platform = self::appConfig($config)['platform'] ?? null;

        return is_string($platform) && $platform !== '' ? $platform : null;
    }

    /**
     * Outbound SuperPDP calls (submit / send-test) are allowed.
     *
     * @param  array<string, mixed>|null  $config
     */
    public static function outboundAllowed(?array $config = null): bool
    {
        return ! self::isProductionBlocked($config);
    }

    /**
     * Public URL to register in the SuperPDP application (no secret).
     *
     * @param  array<string, mixed>|null  $config
     */
    public static function publicWebhookUrl(?array $config = null): string
    {
        $override = self::appConfig($config)['webhook_url'] ?? null;

        if (is_string($override) && $override !== '') {
            return $override;
        }

        return rtrim((string) config('app.url'), '/').'/webhooks/e-invoice/superpdp';
    }

    /**
     * @param  array<string, mixed>|null  $config
     */
    public static function webhookUrlIsHttps(?array $config = null): bool
    {
        return str_starts_with(strtolower(self::publicWebhookUrl($config)), 'https://');
    }

    /**
     * @param  array<string, mixed>|null  $config
     */
    public static function requireHttpsWebhooks(?array $config = null): bool
    {
        return (bool) (self::appConfig($config)['require_https_webhooks'] ?? false);
    }

    /**
     * @param  array<string, mixed>|null  $config
     */
    public static function webhookSecretConfigured(?array $config = null): bool
    {
        $secret = self::superpdpConfig($config)['webhook_secret'] ?? null;

        return is_string($secret) && $secret !== '';
    }

    /**
     * @param  array<string, mixed>|null  $config
     */
    public static function oauthConfigured(?array $config = null): bool
    {
        $superpdp = self::superpdpConfig($config);
        $token = $superpdp['access_token'] ?? null;

        if (is_string($token) && $token !== '') {
            return true;
        }

        [$id, $secret] = self::activeCredentials($superpdp);

        return is_string($id) && $id !== '' && is_string($secret) && $secret !== '';
    }
}
