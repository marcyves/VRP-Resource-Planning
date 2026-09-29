<?php

namespace App\Http\Controllers;

use App\Contracts\ElectronicInvoicePlatform;
use App\Exceptions\ElectronicInvoiceException;
use App\Platforms\SuperPdp\SuperPdpConfig;
use App\Services\ElectronicInvoicing\ElectronicInvoiceMonitor;
use App\Services\ElectronicInvoicing\ElectronicInvoiceService;
use Illuminate\Http\Request;

class ElectronicInvoiceWebhookController extends Controller
{
    public function __invoke(
        Request $request,
        string $platform,
        ElectronicInvoiceService $service,
        ElectronicInvoicePlatform $electronicPlatform,
        ElectronicInvoiceMonitor $monitor,
    ) {
        if ($platform !== 'superpdp') {
            abort(404);
        }

        if (SuperPdpConfig::requireHttpsWebhooks() && ! $request->secure()) {
            $monitor->warning('e-invoice webhook rejected: HTTPS required', [
                'platform' => $platform,
            ]);
            abort(400);
        }

        if (! $request->secure()) {
            $monitor->warning('e-invoice webhook received over HTTP', [
                'platform' => $platform,
                'webhook_url' => SuperPdpConfig::publicWebhookUrl(),
            ]);
        }

        if (! $electronicPlatform->verifyWebhook($request)) {
            abort(401);
        }

        try {
            $event = $electronicPlatform->parseWebhook($request);
            $invoice = $service->applyEvent($event);

            if (! $invoice) {
                $monitor->warning('e-invoice webhook unmatched', [
                    'platform' => $platform,
                    'event' => $event->type->value,
                    'pdp_reference' => $event->pdpReference,
                ]);
            }

            return response()->noContent();
        } catch (ElectronicInvoiceException $e) {
            report($e);
            $monitor->alert(
                'Webhook e-facture rejeté',
                "Plateforme {$platform} : {$e->getMessage()}",
                ['platform' => $platform],
            );

            abort(400);
        } catch (\Throwable $e) {
            report($e);
            $monitor->alert(
                'Webhook e-facture en échec',
                "Plateforme {$platform} : {$e->getMessage()}",
                ['platform' => $platform],
            );

            abort(500);
        }
    }
}
