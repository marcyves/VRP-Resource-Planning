<x-app-layout>
    <x-slot name="header">
        <h2>{{ __('messages.invoices') }} @if($current_year !== 'all'){{ $current_year }}@endif</h2>
    </x-slot>

    <x-treasury-module-tabs active="invoices" />

    <section class="bills-container">
        <form method="get" action="{{ route('treasury.invoices.index') }}" class="invoice-list-filters">
            @if ($sort !== 'id' || $direction !== 'desc')
                <input type="hidden" name="sort" value="{{ $sort }}">
                <input type="hidden" name="direction" value="{{ $direction }}">
            @endif
            <div class="invoice-list-filters__fields">
                <label class="invoice-list-filters__field">
                    <span class="invoice-list-filters__label">{{ __('messages.description') }}</span>
                    <input
                        type="search"
                        name="q"
                        value="{{ $filters['q'] ?? '' }}"
                        placeholder="{{ __('messages.invoice_filter_description') }}"
                        class="form-input"
                    >
                </label>
                <label class="invoice-list-filters__field">
                    <span class="invoice-list-filters__label">{{ __('messages.school') }}</span>
                    <select name="school_id" class="form-input">
                        <option value="">{{ __('messages.invoice_filter_all_clients') }}</option>
                        @foreach ($schools as $school)
                            <option value="{{ $school->id }}" @selected(($filters['school_id'] ?? '') == $school->id)>
                                {{ $school->name }}
                            </option>
                        @endforeach
                    </select>
                </label>
                <label class="invoice-list-filters__field">
                    <span class="invoice-list-filters__label">{{ __('messages.invoice_filter_payment') }}</span>
                    <select name="payment" class="form-input">
                        <option value="">{{ __('messages.invoice_filter_payment_all') }}</option>
                        <option value="paid" @selected(($filters['payment'] ?? '') === 'paid')>{{ __('messages.paid') }}</option>
                        <option value="unpaid" @selected(($filters['payment'] ?? '') === 'unpaid')>{{ __('messages.not_payed') }}</option>
                    </select>
                </label>
            </div>
            <div class="invoice-list-filters__actions">
                <button type="submit" class="btn btn-primary">{{ __('messages.filter') }}</button>
                @if (! empty($filters))
                    <a class="btn btn-secondary" href="{{ route('treasury.invoices.index') }}">{{ __('messages.reset_filters') }}</a>
                @endif
            </div>
        </form>

        <x-table-invoices
            :invoices="$bills"
            :sort="$sort"
            :direction="$direction"
            :filters="$filters"
            :electronic-invoicing-enabled="$electronicInvoicingEnabled"
        />
    </section>
</x-app-layout>
