<x-app-layout>
    <x-slot name="header">
        <h2>{{ __('messages.invoice_create') }}</h2>
    </x-slot>

    <x-invoice-module-tabs active="create" />

    @if (Auth::user()->getMode() == 'Edit')
    <section class="bills-container">
        <form action="{{ route('invoice.store') }}" method="post" class="group-form nice-form nice-form--wide invoice-create-form">
            @csrf

            <p class="form-hint invoice-create-form__meta">
                {{ __('messages.invoice_id') }}: {{ $invoice_id }} · {{ __('messages.date') }}: {{ $bill_date }}
            </p>

            <div class="company-show-grid invoice-create-preview">
                <article class="company-show-card">
                    <h3>{{ $company->name }}</h3>
                    <ul>
                        <li>{{ $company->address }}</li>
                        <li>{{ $company->zip }} {{ $company->city }}</li>
                        <li>{{ $company->country }}</li>
                    </ul>
                    <h3>{{ __('messages.contact') }}</h3>
                    <ul>
                        <li>{{ $company->phone }}</li>
                        <li>{{ $company->email }}</li>
                        @if ($company->website)
                            <li><a href="{{ $company->website }}">{{ $company->website }}</a></li>
                        @endif
                    </ul>
                </article>

                <article class="company-show-card">
                    <h3>{{ __('messages.iban') }}</h3>
                    @if ($company->bank_name)
                        <p class="form-hint">{{ __('messages.bank') }}: {{ $company->bank_name }}</p>
                    @endif
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
                                <td>{{ $company->bank ?: '—' }}</td>
                                <td>{{ $company->branch ?: '—' }}</td>
                                <td>{{ $company->account ?: '—' }}</td>
                                <td>{{ $company->key ?: '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <ul>
                        <li>{{ __('messages.account_holder') }}: {{ $company->iban_name }}</li>
                        <li>{{ __('messages.iban_code') }}: {{ $company->iban }}</li>
                        <li>{{ __('messages.bic_code') }}: {{ $company->bic }}</li>
                    </ul>
                </article>

                <article class="company-show-card">
                    <h3>{{ __('messages.client') }}</h3>
                    <ul>
                        <li>{{ $school->name }}</li>
                        <li>{{ $school->address }}</li>
                        <li>{{ $school->zip }} {{ $school->city }}</li>
                        <li>{{ $school->country }}</li>
                        <li>{{ __('messages.contact') }}: {{ $school->contact }}</li>
                        <li>{{ __('messages.email') }}: {{ $school->email }}</li>
                        <li>{{ __('messages.phone') }}: {{ $school->phone }}</li>
                    </ul>
                </article>

                <article class="company-show-card company-show-card--wide">
                    <h3>{{ __('messages.items') }}</h3>
                    <ul class="invoice-create-items">
                        @foreach ($items as $item)
                            <li class="invoice-create-items__row">
                                @if ($item[4] == 'T')
                                    <strong>{{ htmlspecialchars($item[0]) }}</strong>
                                    <span class="invoice-create-items__meta">{{ __('messages.rate') }}: @money($item[2])€ · {{ __('messages.hours') }}: @money($item[3])</span>
                                @else
                                    <span>{{ htmlspecialchars($item[0]) }}</span>
                                    <span class="invoice-create-items__meta">
                                        @if (is_numeric($item[2]))
                                            {{ $item[1] }} @money($item[2])€ {{ $item[3] }}h
                                        @else
                                            {{ $item[1] }} {{ $item[2] }} {{ $item[3] }}
                                        @endif
                                    </span>
                                @endif
                            </li>
                        @endforeach
                        <li class="invoice-create-items__total">
                            <strong>{{ __('messages.total') }} : @money($total_amount * 1.2) €</strong>
                        </li>
                    </ul>
                </article>
            </div>

            <input type="hidden" name="invoice_id" value="{{ $bill_number }}">
            <input type="hidden" name="school_id" value="{{ $school->id }}">
            @if ($fromSchoolBilling ?? false)
                <input type="hidden" name="from_school_billing" value="1">
            @endif

            <div class="invoice-create-fields">
                <div class="form-group">
                    <x-input-label for="amount">{{ __('messages.amount_including_tax_auto') }}</x-input-label>
                    <x-text-input type="number" step="0.01" name="amount" id="amount" value="{{ $total_amount > 0 ? $total_amount * 1.2 : '' }}" placeholder="{{ __('messages.amount_example') }}" />
                </div>

                <div class="form-group">
                    <x-input-label for="bill_date">{{ __('messages.billing_day') }}</x-input-label>
                    <x-text-input type="text" name="bill_date" id="bill_date" value="{{ $bill_date }}" />
                </div>

                <div class="form-group">
                    <x-input-label for="month">{{ __('messages.month') }}</x-input-label>
                    <x-text-input type="number" name="month" id="month" value="{{ $month }}" min="1" max="12" />
                </div>

                <div class="form-group">
                    <x-input-label for="year">{{ __('messages.year') }}</x-input-label>
                    <x-text-input type="number" name="year" id="year" value="{{ $year }}" />
                </div>

                <div class="form-group invoice-create-fields__description">
                    <x-input-label for="description">{{ __('messages.description') }}</x-input-label>
                    <x-text-input type="text" name="description" id="description" placeholder="{{ __('messages.description') }}" />
                </div>
            </div>

            <div class="form-actions">
                <a class="btn btn-secondary" href="{{ ($fromSchoolBilling ?? false) ? route('school.show', $school).'#billing' : route('invoice.index') }}">{{ __('messages.cancel') }}</a>
                <x-button-primary>{{ __('messages.bill_create') }}</x-button-primary>
            </div>
        </form>
    </section>
    @endif
</x-app-layout>
