@props([
    'schools',
    'month',
    'year',
])

@if ($schools->isNotEmpty())
    <section class="planning-billing-panel" aria-labelledby="planning-billing-title">
        <header class="planning-billing-panel__header">
            <h3 id="planning-billing-title">{{ __('messages.planning_billing_title') }}</h3>
            <p class="form-hint">{{ __('messages.planning_billing_intro') }}</p>
        </header>

        <ul class="planning-billing-panel__list">
            @foreach ($schools as $school)
                @php
                    $hasUnbilled = ($school['unbilled_sessions'] ?? 0) > 0;
                @endphp
                <li class="planning-billing-card {{ $hasUnbilled ? 'planning-billing-card--unbilled' : 'planning-billing-card--billed' }}">
                    <div class="planning-billing-card__main">
                        <strong class="planning-billing-card__name">{{ $school['name'] }}</strong>
                        <span class="planning-billing-card__amount">
                            @if ($hasUnbilled)
                                {{ number_format($school['unbilled_amount_ttc'], 2, ',', ' ') }} € TTC
                                <span class="planning-billing-card__amount-note">{{ __('messages.planning_billing_unbilled_amount') }}</span>
                            @else
                                {{ number_format($school['amount_ttc'], 2, ',', ' ') }} € TTC
                            @endif
                        </span>
                        <span class="planning-billing-card__meta">
                            {{ number_format($school['hours'], 1, ',', ' ') }} {{ __('messages.hours') }}
                            · {{ $school['sessions'] }} {{ __('messages.planning_billing_sessions') }}
                            @if ($hasUnbilled)
                                · {{ $school['unbilled_sessions'] }} {{ __('messages.planning_billing_unbilled') }}
                            @else
                                · {{ __('messages.planning_billing_all_invoiced') }}
                            @endif
                        </span>
                    </div>
                    <a
                        class="btn {{ $hasUnbilled ? 'btn-primary' : 'btn-secondary' }}"
                        href="{{ route('school.show', ['school' => $school['id'], 'focus' => 'billing']) }}#billing"
                    >
                        {{ __('messages.billing') }}
                    </a>
                </li>
            @endforeach
        </ul>
    </section>
@endif
