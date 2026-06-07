@props(['dashboard', 'showYearNote' => false])

@php
    $totals = $dashboard['totals'];
    $months = $dashboard['months'];
    $chart = $dashboard['chart'];
    $year = $dashboard['year'];

    $formatMoney = fn (float $amount): string => number_format($amount, 2, ',', ' ').' €';
@endphp

<section class="invoice-dashboard" aria-labelledby="invoice-dashboard-title">
    <header class="invoice-dashboard__header">
        <h3 id="invoice-dashboard-title">{{ __('messages.invoice_dashboard_title', ['year' => $year]) }}</h3>
        <p class="invoice-dashboard__subtitle">{{ __('messages.invoice_dashboard_subtitle') }}</p>
        @if ($showYearNote)
            <p class="invoice-dashboard__note">{{ __('messages.invoice_dashboard_year_note', ['year' => $year]) }}</p>
        @endif
    </header>

    <div class="invoice-dashboard-kpis">
        <article class="invoice-metric invoice-metric--issued">
            <p class="invoice-metric__title">
                {{ trans_choice('messages.invoice_dashboard_issued_count_label', $totals['issued_count'], ['count' => $totals['issued_count']]) }}
            </p>
            <dl class="invoice-metric__amounts">
                <div class="invoice-metric__amount-row">
                    <dt>{{ __('messages.amount_ht') }}</dt>
                    <dd class="money">{{ $formatMoney($totals['issued_amount_ht']) }}</dd>
                </div>
                <div class="invoice-metric__amount-row">
                    <dt>{{ __('messages.amount_ttc') }}</dt>
                    <dd class="money">{{ $formatMoney($totals['issued_amount']) }}</dd>
                </div>
            </dl>
        </article>
        <article class="invoice-metric invoice-metric--paid">
            <p class="invoice-metric__title">
                {{ trans_choice('messages.invoice_dashboard_collected_count_label', $totals['paid_count'], ['count' => $totals['paid_count']]) }}
            </p>
            <dl class="invoice-metric__amounts">
                <div class="invoice-metric__amount-row">
                    <dt>{{ __('messages.amount_ht') }}</dt>
                    <dd class="money value-income">{{ $formatMoney($totals['paid_amount_ht']) }}</dd>
                </div>
                <div class="invoice-metric__amount-row">
                    <dt>{{ __('messages.amount_ttc') }}</dt>
                    <dd class="money value-income">{{ $formatMoney($totals['paid_amount']) }}</dd>
                </div>
            </dl>
        </article>
        <article class="invoice-metric invoice-metric--planned">
            <p class="invoice-metric__title">
                {{ trans_choice('messages.invoice_dashboard_planned_count_label', $totals['planned_count'], ['count' => $totals['planned_count']]) }}
            </p>
            <dl class="invoice-metric__amounts">
                <div class="invoice-metric__amount-row">
                    <dt>{{ __('messages.amount_ht') }}</dt>
                    <dd class="money value-pending">{{ $formatMoney($totals['planned_amount_ht']) }}</dd>
                </div>
                <div class="invoice-metric__amount-row">
                    <dt>{{ __('messages.amount_ttc') }}</dt>
                    <dd class="money value-pending">{{ $formatMoney($totals['planned_amount_ttc']) }}</dd>
                </div>
            </dl>
        </article>
        <article class="invoice-metric invoice-metric--projected">
            <p class="invoice-metric__title">{{ __('messages.invoice_dashboard_projected_budget') }}</p>
            <p class="invoice-metric__hint">{{ __('messages.invoice_dashboard_projected_budget_hint') }}</p>
            <dl class="invoice-metric__amounts">
                <div class="invoice-metric__amount-row">
                    <dt>{{ __('messages.amount_ht') }}</dt>
                    <dd class="money value-total">{{ $formatMoney($totals['projected_amount_ht']) }}</dd>
                </div>
                <div class="invoice-metric__amount-row">
                    <dt>{{ __('messages.amount_ttc') }}</dt>
                    <dd class="money value-total">{{ $formatMoney($totals['projected_amount_ttc']) }}</dd>
                </div>
            </dl>
        </article>
        <article class="invoice-metric invoice-metric--hours">
            <p class="invoice-metric__title">{{ __('messages.invoice_dashboard_hours_rate_card') }}</p>
            <dl class="invoice-metric__amounts">
                <dd class="invoice-metric__hours-value value-hours">{{ number_format($totals['worked_hours'], 1, ',', ' ') }} h</dd>
                <div class="invoice-metric__amount-row">
                    <dt>{{ __('messages.rate_short') }}</dt>
                    <dd class="value-money">
                        @if ($totals['hourly_rate'] !== null)
                            {{ number_format($totals['hourly_rate'], 2, ',', ' ') }} €/h
                        @else
                            —
                        @endif
                    </dd>
                </div>
            </dl>
            <p class="invoice-metric__hint">{{ __('messages.invoice_dashboard_rate_hint') }}</p>
        </article>
        <article class="invoice-metric invoice-metric--bank">
            <p class="invoice-metric__title">{{ __('messages.bank_balance') }}</p>
            <p class="invoice-metric__value value-total">
                @if ($totals['bank_balance'] !== null)
                    {{ $formatMoney($totals['bank_balance']) }}
                @else
                    —
                @endif
            </p>
            <p class="invoice-metric__hint">{{ __('messages.invoice_dashboard_bank_hint') }}</p>
        </article>
    </div>

    <div class="invoice-dashboard__chart">
        <h4 class="invoice-dashboard__chart-title">{{ __('messages.invoice_dashboard_monthly_chart') }}</h4>
        <div class="treasury-histogram invoice-histogram" aria-label="{{ __('messages.invoice_dashboard_monthly_chart') }}">
            <div class="treasury-histogram__plot">
                @foreach ($chart as $month)
                    <div class="treasury-histogram__month">
                        <div class="treasury-histogram__bars">
                            <span class="treasury-histogram__bar treasury-histogram__bar--issued"
                                style="height: {{ $month['issued_height'] }}%;"
                                title="{{ __('messages.invoices_ttc') }}: {{ number_format($month['issued_amount'], 2, ',', ' ') }} €"></span>
                            <span class="treasury-histogram__bar treasury-histogram__bar--paid"
                                style="height: {{ $month['paid_height'] }}%;"
                                title="{{ __('messages.paid_invoices_ttc') }}: {{ number_format($month['paid_amount'], 2, ',', ' ') }} €"></span>
                            <span class="treasury-histogram__bar treasury-histogram__bar--planned"
                                style="height: {{ $month['planned_height'] }}%;"
                                title="{{ __('messages.planned_amounts') }}: {{ number_format($month['planned_amount_ttc'], 2, ',', ' ') }} €"></span>
                            @if ($month['bank_balance'] !== null)
                                <span class="treasury-histogram__bar treasury-histogram__bar--bank"
                                    style="height: {{ $month['bank_height'] }}%;"
                                    title="{{ __('messages.bank_balance') }}: {{ number_format($month['bank_balance'], 2, ',', ' ') }} €"></span>
                            @endif
                        </div>
                        <span class="treasury-histogram__label">{{ $month['label'] }}</span>
                    </div>
                @endforeach
            </div>
            <div class="treasury-histogram__legend">
                <span><i class="treasury-histogram__swatch treasury-histogram__swatch--issued"></i>{{ __('messages.invoices_ttc') }}</span>
                <span><i class="treasury-histogram__swatch treasury-histogram__swatch--paid"></i>{{ __('messages.paid_invoices_ttc') }}</span>
                <span><i class="treasury-histogram__swatch treasury-histogram__swatch--planned"></i>{{ __('messages.planned_amounts') }}</span>
                <span><i class="treasury-histogram__swatch treasury-histogram__swatch--bank"></i>{{ __('messages.bank_balance') }}</span>
            </div>
        </div>
    </div>

    <div class="invoice-dashboard__table data-table">
        <table class="invoice-monthly-table">
            <thead>
                <tr>
                    <th scope="col">{{ __('messages.month') }}</th>
                    <th scope="col" colspan="2">{{ __('messages.invoice_dashboard_issued') }}</th>
                    <th scope="col" colspan="2">{{ __('messages.invoice_dashboard_collected') }}</th>
                    <th scope="col">{{ __('messages.invoice_dashboard_worked_hours') }}</th>
                    <th scope="col">{{ __('messages.invoice_dashboard_real_hourly_rate') }}</th>
                    <th scope="col" colspan="2">{{ __('messages.invoice_dashboard_planned') }}</th>
                    <th scope="col">{{ __('messages.bank_balance') }}</th>
                </tr>
                <tr class="invoice-monthly-table__subhead">
                    <th scope="col"></th>
                    <th scope="col">{{ __('messages.count_short') }}</th>
                    <th scope="col">{{ __('messages.amount_ttc') }}</th>
                    <th scope="col">{{ __('messages.count_short') }}</th>
                    <th scope="col">{{ __('messages.amount_ttc') }}</th>
                    <th scope="col"></th>
                    <th scope="col"></th>
                    <th scope="col">{{ __('messages.count_short') }}</th>
                    <th scope="col">{{ __('messages.amount_ht') }}</th>
                    <th scope="col"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($months as $month)
                    <tr>
                        <th scope="row">{{ $month['label'] }}</th>
                        <td class="invoice-monthly-table__count">{{ $month['issued_count'] }}</td>
                        <td class="money">@money($month['issued_amount'])</td>
                        <td class="invoice-monthly-table__count">{{ $month['paid_count'] }}</td>
                        <td class="money value-income">@money($month['paid_amount'])</td>
                        <td class="value-hours">{{ number_format($month['worked_hours'], 1, ',', ' ') }} h</td>
                        <td class="value-money">
                            @if ($month['hourly_rate'] !== null)
                                {{ number_format($month['hourly_rate'], 2, ',', ' ') }} €/h
                            @else
                                —
                            @endif
                        </td>
                        <td class="invoice-monthly-table__count">{{ $month['planned_count'] }}</td>
                        <td class="money value-pending">@money($month['planned_amount_ht'])</td>
                        <td class="money value-total">
                            @if ($month['bank_balance'] !== null)
                                @money($month['bank_balance'])
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="invoice-monthly-table__total">
                    <th scope="row">{{ __('messages.total') }}</th>
                    <td class="invoice-monthly-table__count">{{ $totals['issued_count'] }}</td>
                    <td class="money value-total">@money($totals['issued_amount'])</td>
                    <td class="invoice-monthly-table__count">{{ $totals['paid_count'] }}</td>
                    <td class="money value-income">@money($totals['paid_amount'])</td>
                    <td class="value-hours">{{ number_format($totals['worked_hours'], 1, ',', ' ') }} h</td>
                    <td class="value-money">
                        @if ($totals['hourly_rate'] !== null)
                            {{ number_format($totals['hourly_rate'], 2, ',', ' ') }} €/h
                        @else
                            —
                        @endif
                    </td>
                    <td class="invoice-monthly-table__count">{{ $totals['planned_count'] }}</td>
                    <td class="money value-pending">@money($totals['planned_amount_ht'])</td>
                    <td class="money value-total">
                        @if ($totals['bank_balance'] !== null)
                            @money($totals['bank_balance'])
                        @else
                            —
                        @endif
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</section>
