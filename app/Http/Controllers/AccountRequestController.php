<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAccountRequestRequest;
use App\Mail\AccountRequestMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class AccountRequestController extends Controller
{
    public function create(): View
    {
        return view('auth.account-request');
    }

    public function store(StoreAccountRequestRequest $request): RedirectResponse
    {
        $payload = $request->validated();

        $recipient = config('vrp.account_request_email');

        if ($recipient) {
            Mail::to($recipient)->send(new AccountRequestMail($payload));
        } else {
            report(new \RuntimeException('Account request received but VRP_ACCOUNT_REQUEST_EMAIL is not configured.'));
        }

        return redirect()
            ->route('account-request.create')
            ->with('status', __('messages.landing_request_success'));
    }
}
