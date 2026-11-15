<x-app-layout>
    <x-slot name="header">
        <h2>{{ __('messages.nav_invoice') }}</h2>
    </x-slot>

    <section class="page-toolbar">
        <nav class="period-nav" aria-label="{{ __('messages.period') }}">
            <a href="{{ route('billing.prepare.previous') }}" class="period-nav__btn" aria-label="{{ __('pagination.previous') }}">‹</a>
            <span class="period-nav__label">{{ $periodTitle }}</span>
            <a href="{{ route('billing.prepare.next') }}" class="period-nav__btn" aria-label="{{ __('pagination.next') }}">›</a>
        </nav>
    </section>

    @if ($schools->isEmpty())
        <p class="treasury-empty" role="status">{{ __('messages.billing_prepare_empty') }}</p>
    @else
        <section>
        <ul class="resource-grid">
            @foreach ($schools as $school)
                @php
                    $invoicedTtc = round($school['amount_ttc'] - $school['unbilled_amount_ttc'], 2);
                @endphp
                <li class="billing-prepare-card">
                    <h3 class="billing-prepare-card__name">{{ $school['name'] }}</h3>
                    <dl class="school-stats">
                        <div class="school-stat school-stat--invoiced">
                            <dt class="school-stat__label">{{ __('messages.billing_prepare_invoiced') }}</dt>
                            <dd class="school-stat__value">@money($invoicedTtc)</dd>
                        </div>
                        <div class="school-stat school-stat--unbilled">
                            <dt class="school-stat__label">{{ __('messages.billing_prepare_remaining') }}</dt>
                            <dd class="school-stat__value">@money($school['unbilled_amount_ttc'])</dd>
                        </div>
                        <div class="school-stat">
                            <dt class="school-stat__label">{{ __('messages.total') }}</dt>
                            <dd class="school-stat__value">@money($school['amount_ttc'])</dd>
                        </div>
                    </dl>
                    <a
                        class="btn btn-primary"
                        href="{{ route('school.show', ['school' => $school['id'], 'panel' => 'billing']) }}"
                    >
                        {{ __('messages.billing_prepare_action') }}
                    </a>
                </li>
            @endforeach
        </ul>
        </section>
    @endif
</x-app-layout>
