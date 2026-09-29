<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\UpdateElectronicInvoicingSettingsRequest;
use App\Models\PlatformSetting;
use App\Platforms\SuperPdp\SuperPdpConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ElectronicInvoicingSettingsController extends Controller
{
    public function edit(): View
    {
        return view('super-admin.electronic-invoicing.edit', [
            'webhookUrl' => SuperPdpConfig::publicWebhookUrl(),
            'secretStoredInUi' => SuperPdpConfig::storedWebhookSecret() !== null,
            'secretPresentInEnv' => SuperPdpConfig::envWebhookSecret() !== null,
        ]);
    }

    public function update(UpdateElectronicInvoicingSettingsRequest $request): RedirectResponse
    {
        $settings = PlatformSetting::current();

        if ($request->boolean('clear_superpdp_webhook_secret')) {
            $settings->superpdp_webhook_secret = null;
        } elseif ($request->filled('superpdp_webhook_secret')) {
            $settings->superpdp_webhook_secret = $request->input('superpdp_webhook_secret');
        }

        $settings->save();

        session()->flash('success', __('messages.super_admin_webhook_secret_updated'));

        return redirect()->route('super-admin.electronic-invoicing.edit');
    }
}
