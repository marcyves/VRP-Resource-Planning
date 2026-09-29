<?php

namespace App\Http\Controllers;

use App\Contracts\ElectronicInvoicePlatform;
use App\Exceptions\ElectronicInvoiceException;
use App\Services\ElectronicInvoicing\ElectronicInvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ElectronicInvoiceWebhookController extends Controller
{
    public function __invoke(
        Request $request,
        string $platform,
        ElectronicInvoiceService $service,
        ElectronicInvoicePlatform $electronicPlatform,
    ) {
        if ($platform !== 'superpdp') {
            abort(404);
        }

        if (! $electronicPlatform->verifyWebhook($request)) {
            abort(401);
        }

        try {
            $event = $electronicPlatform->parseWebhook($request);
            $invoice = $service->applyEvent($event);

            if (! $invoice) {
                Log::warning('e-invoice webhook unmatched', [
                    'platform' => $platform,
                    'event' => $event->type->value,
                    'pdp_reference' => $event->pdpReference,
                ]);
            }

            return response()->noContent();
        } catch (ElectronicInvoiceException $e) {
            report($e);
            Log::error('e-invoice webhook rejected', [
                'platform' => $platform,
                'message' => $e->getMessage(),
            ]);

            abort(400);
        } catch (\Throwable $e) {
            report($e);
            Log::error('e-invoice webhook failed', [
                'platform' => $platform,
                'message' => $e->getMessage(),
            ]);

            abort(500);
        }
    }
}
