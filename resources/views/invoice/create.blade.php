<x-app-layout>
    <x-slot name="header">
        <h2>{{ __('messages.invoice_create') }}</h2>
    </x-slot>

    @php
        $hasAgendaItems = count($items) > 0;
        $vatMultiplier = \App\Http\Utility\Tools::VAT_MULTIPLIER;
        $prefillHt = old('amount_ht', $total_amount > 0 ? number_format((float) $total_amount, 2, '.', '') : '');
        $prefillTtc = old('amount_ttc', $total_amount > 0 ? number_format((float) $total_amount * $vatMultiplier, 2, '.', '') : '');
        $prefillDescription = old('description', $selectedCourse?->name ?? '');
    @endphp

    @if (Auth::user()->getMode() == 'Edit')
    <section class="bills-container">
        <form
            action="{{ route('invoice.store') }}"
            method="post"
            class="group-form nice-form nice-form--wide invoice-create-form"
            x-data="{
                vat: {{ $vatMultiplier }},
                ht: @js((string) $prefillHt),
                ttc: @js((string) $prefillTtc),
                description: @js((string) $prefillDescription),
                emptyDescription: @js(__('messages.description')),
                requireAmount: @js(! $hasAgendaItems),
                showAmountError: @js($errors->has('amount_ht') || $errors->has('amount_ttc')),
                parse(value) {
                    const n = parseFloat(String(value).replace(',', '.').replace(/\s/g, ''));
                    return Number.isFinite(n) ? n : 0;
                },
                format(n) {
                    return (Math.round(n * 100) / 100).toFixed(2);
                },
                fromHt() {
                    if (this.ht === '' || this.ht === null) {
                        this.ttc = '';
                        return;
                    }
                    this.ttc = this.format(this.parse(this.ht) * this.vat);
                },
                fromTtc() {
                    if (this.ttc === '' || this.ttc === null) {
                        this.ht = '';
                        return;
                    }
                    this.ht = this.format(this.parse(this.ttc) / this.vat);
                },
                get amountValid() {
                    if (! this.requireAmount) {
                        return true;
                    }
                    return this.parse(this.ht) > 0 || this.parse(this.ttc) > 0;
                },
                get htDisplay() {
                    const n = this.parse(this.ht);
                    return n > 0 ? this.format(n).replace('.', ',') : '—';
                },
                get ttcDisplay() {
                    const n = this.parse(this.ttc);
                    return n > 0 ? this.format(n).replace('.', ',') : '—';
                },
                validateSubmit(event) {
                    if (! this.amountValid) {
                        event.preventDefault();
                        this.showAmountError = true;
                    }
                }
            }"
            x-on:submit="validateSubmit"
        >
            @csrf

            <p class="form-hint invoice-create-form__meta">
                {{ __('messages.invoice_id') }}: {{ $invoice_id }} · {{ __('messages.date') }}: {{ $bill_date }}
                · {{ __('messages.invoice_scope') }}:
                @if ($selectedCourse ?? null)
                    {{ $selectedCourse->name }}
                @else
                    {{ __('messages.invoice_scope_all_month') }}
                @endif
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
                    <h3>{{ __('messages.billing_bank_account') }}</h3>
                    <x-company-billing-details :account="$company->billingBankAccount" />
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
                        @if ($hasAgendaItems)
                            @foreach ($items as $item)
                                <li class="invoice-create-items__row">
                                    @if (($item[5] ?? $item[4] ?? '') == 'T')
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
                                <strong>{{ __('messages.total') }} : @money($total_amount * $vatMultiplier) €</strong>
                            </li>
                        @else
                            <li class="invoice-create-items__row">
                                <strong x-text="description || emptyDescription"></strong>
                                <span class="invoice-create-items__meta">
                                    {{ __('messages.amount_ht') }} :
                                    <span x-text="htDisplay"></span> €
                                    · {{ __('messages.amount_ttc') }} :
                                    <span x-text="ttcDisplay"></span> €
                                </span>
                            </li>
                            <li class="invoice-create-items__total">
                                <strong>{{ __('messages.total_ttc') }} : <span x-text="ttcDisplay"></span> €</strong>
                            </li>
                        @endif
                    </ul>
                </article>
            </div>

            <input type="hidden" name="invoice_id" value="{{ $bill_number }}">
            <input type="hidden" name="school_id" value="{{ $school->id }}">
            @if ($fromSchoolBilling ?? false)
                <input type="hidden" name="from_school_billing" value="1">
                <input type="hidden" name="course_id" value="{{ $courseId ?? '' }}">
            @endif

            <div class="invoice-create-fields">
                <div class="invoice-amount-pair">
                    <div class="form-group">
                        <x-input-label for="amount_ht">{{ __('messages.invoice_amount_ht') }}</x-input-label>
                        <input
                            class="form-input"
                            type="text"
                            inputmode="decimal"
                            name="amount_ht"
                            id="amount_ht"
                            x-bind:value="ht"
                            x-on:input="ht = $event.target.value; fromHt()"
                            value="{{ $prefillHt }}"
                            placeholder="{{ __('messages.amount_example') }}"
                            @if ($hasAgendaItems) readonly @endif
                            autocomplete="off"
                        >
                    </div>
                    <div class="form-group">
                        <x-input-label for="amount_ttc">{{ __('messages.invoice_amount_ttc') }}</x-input-label>
                        <input
                            class="form-input"
                            type="text"
                            inputmode="decimal"
                            name="amount_ttc"
                            id="amount_ttc"
                            x-bind:value="ttc"
                            x-on:input="ttc = $event.target.value; fromTtc()"
                            value="{{ $prefillTtc }}"
                            placeholder="{{ __('messages.amount_example') }}"
                            @if ($hasAgendaItems) readonly @endif
                            autocomplete="off"
                        >
                    </div>
                    <p class="form-hint invoice-amount-pair__hint">
                        {{ __('messages.invoice_amount_vat_hint', ['rate' => \App\Http\Utility\Tools::vatRateLabel()]) }}
                    </p>
                    <p class="form-error" x-show="showAmountError && !amountValid" x-cloak>
                        {{ __('messages.invoice_amount_must_be_positive') }}
                    </p>
                    <x-input-error :messages="$errors->get('amount_ht')" />
                    <x-input-error :messages="$errors->get('amount_ttc')" />
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

                @if (! ($fromSchoolBilling ?? false))
                <div class="form-group invoice-create-fields__description">
                    <x-input-label for="course_id">{{ __('messages.invoice_scope') }}</x-input-label>
                    <select name="course_id" id="course_id" class="form-input">
                        <option value="">{{ __('messages.invoice_scope_all_month') }}</option>
                        @foreach ($courses ?? [] as $courseOption)
                            <option value="{{ $courseOption->id }}" @selected(($courseId ?? null) == $courseOption->id)>{{ $courseOption->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="form-group invoice-create-fields__description">
                    <x-input-label for="description">{{ __('messages.description') }}</x-input-label>
                    <x-text-input
                        type="text"
                        name="description"
                        id="description"
                        x-model="description"
                        value="{{ $prefillDescription }}"
                        placeholder="{{ __('messages.description') }}"
                    />
                </div>

                <div class="form-group invoice-create-fields__comment">
                    <x-input-label for="comment">{{ __('messages.invoice_comment') }}</x-input-label>
                    <textarea id="comment" name="comment" rows="3" class="form-input" maxlength="2000">{{ old('comment') }}</textarea>
                    <p class="form-hint">{{ __('messages.invoice_comment_help') }}</p>
                    <x-input-error :messages="$errors->get('comment')" />
                </div>
            </div>

            <div class="form-actions">
                <a class="btn btn-secondary" href="{{ ($fromSchoolBilling ?? false) ? route('school.show', ['school' => $school, 'panel' => 'billing']) : route('treasury.invoices.index') }}">{{ __('messages.cancel') }}</a>
                <x-button-primary>{{ __('messages.bill_create') }}</x-button-primary>
            </div>
        </form>
    </section>
    @endif
</x-app-layout>
