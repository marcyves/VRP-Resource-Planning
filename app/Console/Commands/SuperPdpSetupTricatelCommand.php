<?php

namespace App\Console\Commands;

use App\Enums\ElectronicInvoiceStatus;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\School;
use App\Platforms\SuperPdp\SuperPdpAuth;
use App\Platforms\SuperPdp\SuperPdpClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class SuperPdpSetupTricatelCommand extends Command
{
    protected $signature = 'superpdp:setup-tricatel
                            {--company= : ID société VRP (défaut : XDM Consulting)}
                            {--invoice=26901 : Numéro facture test à créer}
                            {--amount=1200 : Montant TTC}';

    protected $description = 'Crée le client sandbox Tricatel (PEPPOL) et une facture prête à émettre';

    public function handle(): int
    {
        $company = $this->resolveCompany();

        if (! $company) {
            return self::FAILURE;
        }

        $school = School::query()->updateOrCreate(
            [
                'company_id' => $company->id,
                'siren' => '000000001',
            ],
            [
                'name' => 'Tricatel',
                'code' => 'TRICATEL',
                'address' => 'Avenue de la République',
                'city' => 'Chambray-lès-Tours',
                'zip' => '37170',
                'country' => 'France',
                'electronic_address' => (string) config(
                    'electronic-invoicing.superpdp.sandbox_buyer_electronic_address',
                    '315143296_12712',
                ),
            ],
        );

        $invoiceId = (string) $this->option('invoice');
        $pdfPath = 'invoices/'.$company->bill_prefix.$invoiceId.'.pdf';

        $invoice = Invoice::query()->updateOrCreate(
            ['id' => $invoiceId],
            [
                'description' => 'Facture test sandbox — Tricatel',
                'bill_date' => now()->toDateString(),
                'amount' => (float) $this->option('amount'),
                'company_id' => $company->id,
                'school_id' => $school->id,
                'electronic_invoice_status' => ElectronicInvoiceStatus::Ready,
                'pdp_reference' => null,
                'electronic_status_at' => now(),
                'rejection_reason' => null,
            ],
        );

        if (! Storage::exists($pdfPath)) {
            Storage::put($pdfPath, '%PDF-1.4'.PHP_EOL.'% Facture test e-facture Tricatel'.PHP_EOL);
        }

        $this->info('Client sandbox Tricatel prêt (school #'.$school->id.').');
        $this->line('  SIREN : '.$school->siren);
        $this->line('  Adresse PEPPOL (0225) : '.$school->electronic_address);
        $this->newLine();
        $this->info('Facture prête : '.$company->bill_prefix.$invoice->id);
        $this->line('  PDF : storage/app/'.$pdfPath);
        $this->newLine();
        $this->warn($this->environmentHint());
        $this->comment('Émission : php artisan superpdp:send-test --invoice='.$invoiceId);

        return self::SUCCESS;
    }

    private function environmentHint(): string
    {
        if ((string) config('electronic-invoicing.superpdp.env', 'production') === 'sandbox') {
            return 'SUPERPDP_ENV=sandbox — prêt pour Tricatel (annuaire bac à sable).';
        }

        try {
            $auth = app(SuperPdpAuth::class);
            if (! $auth->isConfigured()) {
                return 'SuperPDP non configuré.';
            }

            $profile = (new SuperPdpClient(config('electronic-invoicing.superpdp.base_url'), $auth))->companyMe();
            if (($profile['env'] ?? '') === 'production') {
                return 'Attention : credentials PRODUCTION actifs. Tricatel n\'existe que dans le bac à sable.'
                    .' Créez une Application « sandbox » dans SuperPDP et définissez SUPERPDP_ENV=sandbox'
                    .' + SUPERPDP_SANDBOX_CLIENT_ID/SECRET dans .env.';
            }
        } catch (\Throwable) {
            return 'Impossible de lire companies/me — vérifiez SUPERPDP_ENV et les credentials.';
        }

        return 'Credentials alignés avec le bac à sable SuperPDP.';
    }

    private function resolveCompany(): ?Company
    {
        $companyId = $this->option('company');

        if ($companyId) {
            $company = Company::query()->find($companyId);

            if (! $company) {
                $this->error("Société {$companyId} introuvable.");

                return null;
            }

            return $company;
        }

        $company = Company::query()
            ->where('siren', '823053699')
            ->orWhere('bill_prefix', 'XDM')
            ->first();

        if (! $company) {
            $this->error('Société XDM introuvable. Passez --company=<id>.');

            return null;
        }

        return $company;
    }
}
