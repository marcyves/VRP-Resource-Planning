<?php

namespace App\Platforms\SuperPdp;

class SuperPdpConfig
{
    /**
     * @param  array<string, mixed>  $config
     * @return array{0: ?string, 1: ?string, 2: string}
     */
    public static function activeCredentials(array $config): array
    {
        $env = (string) ($config['env'] ?? 'production');

        if ($env === 'sandbox') {
            return [
                $config['sandbox_client_id'] ?? $config['client_id'] ?? null,
                $config['sandbox_client_secret'] ?? $config['client_secret'] ?? null,
                'sandbox',
            ];
        }

        return [
            $config['client_id'] ?? null,
            $config['client_secret'] ?? null,
            'production',
        ];
    }
}
