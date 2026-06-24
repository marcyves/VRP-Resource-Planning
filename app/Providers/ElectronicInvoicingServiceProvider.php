<?php

namespace App\Providers;

use App\Contracts\ElectronicInvoicePlatform;
use App\Platforms\NullElectronicInvoicePlatform;
use App\Platforms\SuperPdp\SuperPdpAuth;
use App\Platforms\SuperPdp\SuperPdpClient;
use App\Platforms\SuperPdp\SuperPdpConfig;
use App\Platforms\SuperPdp\SuperPdpPlatform;
use App\Services\ElectronicInvoicing\ElectronicInvoiceCiiBuilder;
use App\Services\ElectronicInvoicing\ElectronicInvoiceValidator;
use Illuminate\Support\ServiceProvider;

class ElectronicInvoicingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SuperPdpAuth::class, function () {
            $config = config('electronic-invoicing.superpdp');
            [$clientId, $clientSecret] = SuperPdpConfig::activeCredentials($config);

            return new SuperPdpAuth(
                $config['base_url'],
                is_string($clientId) ? $clientId : null,
                is_string($clientSecret) ? $clientSecret : null,
                $config['access_token'],
            );
        });

        $this->app->singleton(ElectronicInvoicePlatform::class, function ($app) {
            $platform = config('electronic-invoicing.platform');
            $validator = $app->make(ElectronicInvoiceValidator::class);

            if ($platform === 'superpdp') {
                $auth = $app->make(SuperPdpAuth::class);
                $client = $auth->isConfigured()
                    ? new SuperPdpClient(config('electronic-invoicing.superpdp.base_url'), $auth)
                    : null;

                return new SuperPdpPlatform(
                    $client,
                    $validator,
                    $app->make(ElectronicInvoiceCiiBuilder::class),
                    config('electronic-invoicing.superpdp.webhook_secret'),
                );
            }

            return new NullElectronicInvoicePlatform;
        });
    }
}
