<?php

namespace App\Http\Controllers;

use App\Exceptions\ElectronicInvoiceException;
use App\Models\Invoice;
use App\Services\ElectronicInvoicing\ElectronicInvoiceService;
use Illuminate\Http\Request;

class ElectronicInvoiceWebhookController extends Controller
{
    public function __invoke(Request $request, string $platform, ElectronicInvoiceService $service)
    {
        if ($platform !== 'superpdp') {
            abort(404);
        }

        $electronicPlatform = app(\App\Contracts\ElectronicInvoicePlatform::class);

        if (! $electronicPlatform->verifyWebhook($request)) {
            abort(401);
        }

        $event = $electronicPlatform->parseWebhook($request);
        $service->applyEvent($event);

        return response()->noContent();
    }
}
