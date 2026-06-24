<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Platforms\SuperPdp\SuperPdpAuth;
use App\Platforms\SuperPdp\SuperPdpClient;
use App\Services\ElectronicInvoicing\ElectronicInvoiceService;
use Illuminate\Console\Command;

class SuperPdpSendTestCommand extends Command
{
    protected $signature = 'superpdp:send-test
                            {--format=factur-x : factur-x, cii or ubl}
                            {--invoice= : Optional VRP invoice id (numeric part) to submit instead}';

    protected $description = 'Envoie une facture test SuperPDP (sandbox) ou une facture VRP prête';

    public function handle(SuperPdpAuth $auth, ElectronicInvoiceService $electronicInvoiceService): int
    {
        if (! $auth->isConfigured()) {
            $this->error('SuperPDP non configuré (.env).');

            return self::FAILURE;
        }

        $client = new SuperPdpClient(config('electronic-invoicing.superpdp.base_url'), $auth);

        if ($invoiceId = $this->option('invoice')) {
            return $this->submitVrpInvoice($electronicInvoiceService, (string) $invoiceId);
        }

        $format = (string) $this->option('format');
        $externalId = 'VRP-TEST-'.now()->format('Ymd-His');

        try {
            $this->info("Génération facture test SuperPDP ({$format})…");
            $content = $client->generateTestInvoice($format);
            $contentType = $format === 'factur-x' ? 'application/pdf' : 'application/xml';

            $this->info('Envoi vers SuperPDP…');
            $result = $client->sendInvoice($content, $contentType, $externalId);

            $pdpId = $result['id'] ?? '?';
            $this->info("OK — facture sandbox #{$pdpId} (external_id: {$externalId})");

            if (isset($result['events'][0]['status_text'])) {
                $this->line('Statut : '.$result['events'][0]['status_text']);
            }

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }

    private function submitVrpInvoice(ElectronicInvoiceService $service, string $invoiceId): int
    {
        $invoice = Invoice::query()->find($invoiceId);

        if (! $invoice) {
            $this->error("Facture {$invoiceId} introuvable.");

            return self::FAILURE;
        }

        try {
            $updated = $service->submit($invoice);
            $invoice->loadMissing('company');

            $this->info('OK — '.($updated->company->bill_prefix ?? '').$updated->id);
            $this->line('Statut e-facture : '.$updated->electronic_invoice_status->label());
            $this->line('Réf. SuperPDP : '.$updated->pdp_reference);

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            if ($e instanceof \App\Exceptions\ElectronicInvoiceException && $e->errors !== []) {
                foreach ($e->errors as $error) {
                    $this->warn('• '.$error);
                }
            }

            return self::FAILURE;
        }
    }
}
