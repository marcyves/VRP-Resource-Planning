<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Platforms\SuperPdp\SuperPdpAuth;
use App\Platforms\SuperPdp\SuperPdpConfig;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class SuperPdpGoLiveCheckCommand extends Command
{
    protected $signature = 'superpdp:go-live-check';

    protected $description = 'Checklist go-live SuperPDP (aucun secret affiché)';

    public function handle(SuperPdpAuth $auth): int
    {
        $webhookUrl = SuperPdpConfig::publicWebhookUrl();
        $allowProduction = SuperPdpConfig::allowProduction();
        $env = SuperPdpConfig::environment();
        $platform = SuperPdpConfig::platform() ?? '(vide / driver Null)';

        $this->info('VRP — contrôle go-live e-facture (valeurs masquées)');
        $this->line('APP_URL : '.config('app.url'));
        $this->line('E_INVOICE_PLATFORM : '.$platform);
        $this->line('SUPERPDP_ENV : '.$env);
        $this->line('E_INVOICE_ALLOW_PRODUCTION : '.($allowProduction ? 'true' : 'false'));
        $this->line('OAuth / token configuré : '.($auth->isConfigured() && SuperPdpConfig::oauthConfigured() ? 'oui' : 'non'));
        $this->line('Secret webhook présent : '.(SuperPdpConfig::webhookSecretConfigured() ? 'oui' : 'non'));
        $this->line('URL webhook à déclarer chez SuperPDP : '.$webhookUrl);
        $this->line('URL webhook en HTTPS : '.(SuperPdpConfig::webhookUrlIsHttps() ? 'oui' : 'NON'));
        $this->line('Exiger HTTPS sur POST webhook : '.(SuperPdpConfig::requireHttpsWebhooks() ? 'oui' : 'non'));
        $this->line('TRUSTED_PROXIES : '.(env('TRUSTED_PROXIES') ?: '(non défini)'));
        $this->line('E-mail d’alerte : '.(filled(config('electronic-invoicing.alert_email')) ? 'oui' : 'non (logs seulement)'));
        $this->line('Verrou production : '.(SuperPdpConfig::isProductionBlocked() ? 'FERMÉ (pas d’émission live)' : 'ouvert ou sandbox'));

        if (! Schema::hasColumn('companies', 'electronic_invoicing_enabled')) {
            $this->warn('Colonne electronic_invoicing_enabled absente — appliquer la migration / ALTER SQL du runbook go-live.');

            if ($env === 'production' && ! $allowProduction) {
                $this->warn(__('messages.electronic_invoice_production_blocked'));
            }

            if (! SuperPdpConfig::webhookUrlIsHttps()) {
                $this->warn('Mettre APP_URL (ou E_INVOICE_WEBHOOK_URL) en https:// avant le go-live.');
            }

            return self::SUCCESS;
        }

        $enabled = Company::query()
            ->where('electronic_invoicing_enabled', true)
            ->orderBy('name')
            ->get(['id', 'name', 'bill_prefix']);

        if ($enabled->isEmpty()) {
            $this->warn('Aucune société avec l’interrupteur locataire activé.');
        } else {
            $this->line('Sociétés opt-in ('.$enabled->count().') :');
            foreach ($enabled as $company) {
                $this->line('  #'.$company->id.' '.$company->bill_prefix.' '.$company->name);
            }
        }

        if ($env === 'production' && ! $allowProduction) {
            $this->warn(__('messages.electronic_invoice_production_blocked'));
        }

        if (! SuperPdpConfig::webhookUrlIsHttps()) {
            $this->warn('Mettre APP_URL (ou E_INVOICE_WEBHOOK_URL) en https:// avant le go-live.');
        }

        if ($allowProduction && $enabled->count() > 1) {
            $this->warn('Le verrou process est ouvert et plusieurs locataires sont opt-in. Vérifier que c’est voulu.');
        }

        return self::SUCCESS;
    }
}
