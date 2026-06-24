<?php

namespace App\Console\Commands;

use App\Platforms\SuperPdp\SuperPdpAuth;
use App\Platforms\SuperPdp\SuperPdpClient;
use App\Platforms\SuperPdp\SuperPdpConfig;
use Illuminate\Console\Command;

class SuperPdpTestCommand extends Command
{
    protected $signature = 'superpdp:test';

    protected $description = 'Test la connexion OAuth et récupère le profil entreprise SuperPDP';

    public function handle(SuperPdpAuth $auth): int
    {
        if (! $auth->isConfigured()) {
            $this->error('SuperPDP non configuré. Définissez E_INVOICE_PLATFORM=superpdp et SUPERPDP_CLIENT_ID / SUPERPDP_CLIENT_SECRET dans .env');

            return self::FAILURE;
        }

        try {
            $token = $auth->accessToken();
            $this->info('OAuth OK — token obtenu ('.strlen($token).' caractères).');

            $configuredEnv = (string) config('electronic-invoicing.superpdp.env', 'production');
            [, , $credentialEnv] = SuperPdpConfig::activeCredentials(config('electronic-invoicing.superpdp'));
            $this->line("Credentials VRP : {$credentialEnv} (SUPERPDP_ENV={$configuredEnv})");

            $client = new SuperPdpClient(config('electronic-invoicing.superpdp.base_url'), $auth);
            $company = $client->companyMe();

            $this->line('Entreprise API : '.($company['formal_name'] ?? '?')
                .' — SIREN '.($company['number'] ?? '?')
                .' — env '.($company['env'] ?? '?'));

            $this->line(json_encode($company, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
