@props(['account' => null])

@php
    $hasRib = $account && ($account->rib_bank_code || $account->rib_branch_code || $account->rib_account_number || $account->rib_key);
@endphp

@if (! $account)
    <p class="form-hint">{{ __('messages.billing_bank_account_none') }}</p>
@else
    @if ($account->bank?->name)
        <p class="form-hint">{{ __('messages.bank') }}: {{ $account->bank->name }}</p>
    @endif
    @if ($hasRib)
        <table class="simple-table">
            <thead>
                <tr>
                    <th>{{ __('messages.bank_code') }}</th>
                    <th>{{ __('messages.branch_code') }}</th>
                    <th>{{ __('messages.account_number') }}</th>
                    <th>{{ __('messages.key') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $account->rib_bank_code ?: '—' }}</td>
                    <td>{{ $account->rib_branch_code ?: '—' }}</td>
                    <td>{{ $account->rib_account_number ?: '—' }}</td>
                    <td>{{ $account->rib_key ?: '—' }}</td>
                </tr>
            </tbody>
        </table>
    @endif
    <ul>
        <li>{{ __('messages.account_holder') }}: {{ $account->iban_holder ?: __('messages.not_provided') }}</li>
        <li>{{ __('messages.iban_code') }}: {{ $account->iban ?: __('messages.not_provided') }}</li>
        <li>{{ __('messages.bic_code') }}: {{ $account->bic ?: __('messages.not_provided') }}</li>
    </ul>
@endif
