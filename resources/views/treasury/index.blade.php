<x-app-layout>
    <x-slot name="header">
        <h2>{{ __('messages.treasury') }} {{ $year }}</h2>
    </x-slot>

    <x-treasury-module-tabs />

    <x-kpi-grid :items="[
        ['icon' => 'receipt', 'label' => __('messages.invoices_ttc'), 'value' => number_format($invoiceTotal, 2, ',', ' ') . ' €'],
        ['icon' => 'wallet', 'label' => __('messages.closing_balance'), 'value' => number_format($closingBalance, 2, ',', ' ') . ' €'],
        ['icon' => 'chart', 'label' => __('messages.paid_invoices_ttc'), 'value' => number_format($invoicePaidTotal, 2, ',', ' ') . ' €', 'variant' => 'success'],
    ]" />

    <section id="treasury-summary">
        <header class="treasury-section-header">
            <h3>{{ __('messages.monthly_treasury_histogram') }}</h3>
        </header>
        <div class="treasury-histogram" aria-label="{{ __('messages.monthly_treasury_histogram') }}">
            <div class="treasury-histogram__plot">
                @foreach($monthlyChartData as $month)
                    <div class="treasury-histogram__month">
                        <div class="treasury-histogram__bars">
                            <span class="treasury-histogram__bar treasury-histogram__bar--issued" style="height: {{ $month['issued_height'] }}%;" title="{{ __('messages.invoices_ttc') }}: @money($month['issued'])"></span>
                            <span class="treasury-histogram__bar treasury-histogram__bar--paid" style="height: {{ $month['paid_height'] }}%;" title="{{ __('messages.paid_invoices_ttc') }}: @money($month['paid'])"></span>
                            <span class="treasury-histogram__bar treasury-histogram__bar--spent" style="height: {{ $month['spent_height'] }}%;" title="{{ __('messages.expenses') }}: @money($month['spent'])"></span>
                            <span class="treasury-histogram__bar treasury-histogram__bar--planned" style="height: {{ $month['planned_height'] }}%;" title="{{ __('messages.planned_amounts') }}: @money($month['planned'])"></span>
                        </div>
                        <span class="treasury-histogram__label">{{ $month['label'] }}</span>
                    </div>
                @endforeach
            </div>
            <div class="treasury-histogram__legend">
                <span><i class="treasury-histogram__swatch treasury-histogram__swatch--issued"></i>{{ __('messages.invoices_ttc') }}</span>
                <span><i class="treasury-histogram__swatch treasury-histogram__swatch--paid"></i>{{ __('messages.paid_invoices_ttc') }}</span>
                <span><i class="treasury-histogram__swatch treasury-histogram__swatch--spent"></i>{{ __('messages.expenses') }}</span>
                <span><i class="treasury-histogram__swatch treasury-histogram__swatch--planned"></i>{{ __('messages.planned_amounts') }}</span>
            </div>
        </div>
    </section>

    <section>
        <div class="treasury-summary-grid">
            <article>
                <h3>{{ __('messages.invoices_ttc') }}</h3>
                <strong>@money($invoiceTotal)</strong>
            </article>
            <article>
                <h3>{{ __('messages.opening_balance') }}</h3>
                @if (Auth::user()->getMode() == 'Edit')
                    <form class="treasury-balance-form nice-form nice-form--embedded" method="post" action="{{ route('treasury.balance.update') }}">
                        @csrf
                        <div class="treasury-balance-form__fields">
                            <div class="form-group">
                                <x-input-label for="opening_date">{{ __('messages.date') }}</x-input-label>
                                <x-text-input type="date" name="opening_date" id="opening_date" value="{{ $treasuryBalance->opening_date->format('Y-m-d') }}" />
                            </div>
                            <div class="form-group">
                                <x-input-label for="opening_amount">{{ __('messages.amount') }}</x-input-label>
                                <x-text-input class="treasury-balance-input--amount" type="number" step="0.01" name="opening_amount" id="opening_amount" value="{{ $treasuryBalance->opening_amount }}" />
                            </div>
                        </div>
                        <div class="form-actions">
                            <x-button-primary class="btn--compact">{{ __('messages.save') }}</x-button-primary>
                        </div>
                    </form>
                @else
                    <span>@formatDate($treasuryBalance->opening_date)</span>
                    <strong>@money($treasuryBalance->opening_amount)</strong>
                @endif
            </article>
            <article class="treasury-summary-card--wide">
                <h3>{{ __('messages.closing_balance') }}</h3>
                <table class="treasury-summary-table">
                    <tbody>
                        <tr>
                            <th>{{ __('messages.paid_invoices_ttc') }}</th>
                            <td class="money">@money($invoicePaidTotal)</td>
                        </tr>
                        <tr class="treasury-summary-table__group">
                            <th>{{ __('messages.expense_reports') }}</th>
                            <td></td>
                        </tr>
                        <tr>
                            <th>{{ __('messages.submitted') }}</th>
                            <td class="money">- @money($submittedExpenseReportTotal)</td>
                        </tr>
                        <tr>
                            <th>{{ __('messages.validated') }}</th>
                            <td class="money">- @money($validatedExpenseReportTotal)</td>
                        </tr>
                        <tr>
                            <th>{{ __('messages.paid') }}</th>
                            <td class="money">- @money($paidExpenseReportTotal)</td>
                        </tr>
                        <tr>
                            <th>{{ __('messages.standalone_expenses') }}</th>
                            <td class="money">- @money($standaloneTotal)</td>
                        </tr>
                        <tr class="treasury-summary-table__total">
                            <th>{{ __('messages.closing_balance') }}</th>
                            <td class="money">@money($closingBalance)</td>
                        </tr>
                    </tbody>
                </table>
            </article>
        </div>
    </section>

    <section id="expense-reports">
        <header class="treasury-section-header">
            <h3>{{ __('messages.expense_reports') }}</h3>
        </header>

        @if($reports->isEmpty())
            <p class="treasury-empty">{{ __('messages.no_expense_report') }}</p>
        @else
            <div class="treasury-report-list">
                @foreach($reports as $report)
                    <a class="treasury-report-card" href="{{ route('treasury.reports.show', $report) }}">
                        <div class="treasury-report-card__main">
                            <h4>@monthName($report->month) {{ $report->year }}</h4>
                            <strong>@money($report->expenses->sum('amount'))</strong>
                        </div>
                        <div class="treasury-report-card__meta">
                            <span>{{ $report->expenses->count() }} {{ __('messages.expenses') }}</span>
                            <span class="status-chip treasury-status treasury-status--{{ $report->status }}">
                                {{ __('messages.expense_report_status_' . $report->status) }}
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>

    <section id="standalone-expenses">
        <header class="treasury-section-header">
            <h3>{{ __('messages.expenses') }}</h3>
        </header>

        @if($standaloneExpenses->isEmpty())
            <p class="treasury-empty">{{ __('messages.no_standalone_expense') }}</p>
        @else
            <div class="data-table">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('messages.date') }}</th>
                        <th>{{ __('messages.payment_date') }}</th>
                        <th>{{ __('messages.description') }}</th>
                        <th>{{ __('messages.category') }}</th>
                        <th>{{ __('messages.amount') }}</th>
                        <th>{{ __('messages.recurring') }}</th>
                        @if(Auth::user()->getMode() == "Edit")
                            <th>{{ __('messages.actions') }}</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($standaloneExpenses as $expense)
                    <tr>
                        <td>@formatDate($expense->expense_date)</td>
                        <td>@formatDate($expense->payment_date)</td>
                        <td>{{ $expense->label }}</td>
                        <td>{{ $expense->category }}</td>
                        <td class="money">@money($expense->amount)</td>
                        <td>{{ $expense->is_recurring ? __('messages.yes') : __('messages.no') }}</td>
                        @if(Auth::user()->getMode() == "Edit")
                        <td class="card-actions">
                            <form class="inline-form" action="{{ route('treasury.expenses.edit', $expense) }}" method="get">
                                <x-button-edit />
                            </form>
                            <form class="inline-form" action="{{ route('treasury.expenses.destroy', $expense) }}" method="post">
                                @csrf
                                @method('delete')
                                <x-button-delete />
                            </form>
                        </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        @endif
    </section>
</x-app-layout>
