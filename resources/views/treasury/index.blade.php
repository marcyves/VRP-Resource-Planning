<x-app-layout>
    <x-slot name="header">
        <h2>{{ __('messages.treasury') }} {{ $year }}</h2>
    </x-slot>

    <x-treasury-module-tabs active="summary" />

    <x-invoice-dashboard
        :dashboard="$dashboard"
        :show-year-note="$current_year === 'all'"
    />

    <section id="treasury-balance">
        <div class="treasury-summary-grid">
            <article class="treasury-summary-card--wide">
                <header class="treasury-section-header">
                    <h3>{{ __('messages.balance_closing') }}</h3>
                </header>
                <table class="treasury-summary-table">
                    <tbody>
                        <tr>
                            <th>{{ __('messages.paid_invoices_ttc') }}</th>
                            <td class="money value-income">@money($invoicePaidTotal)</td>
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
                            <th>{{ __('messages.balance_closing') }}</th>
                            <td class="money value-total">@money($closingBalance)</td>
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
