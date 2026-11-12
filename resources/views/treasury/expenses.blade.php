<x-app-layout>
    <x-slot name="header">
        <h2>{{ __('messages.expenses') }} @if($current_year !== 'all'){{ $year }}@endif</h2>
    </x-slot>

    <section class="page-toolbar">
        <x-year-filter />
        @if(Auth::user()->getMode() == "Edit")
            <div class="page-toolbar__actions">
                <a class="btn btn-primary" href="{{ route('treasury.expenses.create') }}">{{ __('messages.expense_create') }}</a>
            </div>
        @endif
    </section>

    <section id="expense-reports">
        <header class="treasury-section-header">
            <h3>{{ __('messages.expense_reports') }}</h3>
        </header>

        @if($reports->isEmpty())
            <p class="treasury-empty"><x-empty-state icon="cart">{{ __('messages.no_expense_report') }}</x-empty-state></p>
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
            <p class="treasury-empty"><x-empty-state icon="cart">{{ __('messages.no_standalone_expense') }}</x-empty-state></p>
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
