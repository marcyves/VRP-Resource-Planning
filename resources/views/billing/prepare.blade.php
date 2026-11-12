<x-app-layout>
    <x-slot name="header">
        <h2>{{ __('messages.nav_invoice') }} {{ $periodTitle }}</h2>
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
        <ul class="planning-billing-panel__list">
            @foreach ($schools as $school)
                <li class="planning-billing-card planning-billing-card--unbilled">
                    <div class="planning-billing-card__main">
                        <strong class="planning-billing-card__name">{{ $school['name'] }}</strong>
                        <span class="planning-billing-card__amount">
                            {{ number_format($school['unbilled_amount_ttc'], 2, ',', ' ') }} € TTC
                            <span class="planning-billing-card__amount-note">{{ __('messages.planning_billing_unbilled_amount') }}</span>
                        </span>
                        <span class="planning-billing-card__meta">
                            {{ number_format($school['hours'], 1, ',', ' ') }} {{ __('messages.hours') }}
                            · {{ $school['unbilled_sessions'] }} {{ __('messages.planning_billing_unbilled') }}
                        </span>
                    </div>
                    <a
                        class="btn btn-primary"
                        href="{{ route('school.show', ['school' => $school['id'], 'panel' => 'billing']) }}"
                    >
                        {{ __('messages.billing_prepare_action') }}
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
</x-app-layout>
