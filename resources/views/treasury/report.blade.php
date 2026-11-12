<x-app-layout>
    <x-slot name="header">
        <h2>{{ __('messages.expense_report_detail') }} @monthName($expenseReport->month) {{ $expenseReport->year }}</h2>
    </x-slot>

    <p class="page-toolbar__actions">
        <a class="btn btn-secondary" href="{{ route('treasury.expenses.index') }}">{{ __('messages.expenses') }}</a>
    </p>

    <section>
        @php
            $taxRate = fn ($expense) => (float) ($expense->tax_amount ?? 20);
            $amountHt = fn ($expense) => $expense->amount / (1 + ($taxRate($expense) / 100));
            $totalTtc = $expenseReport->expenses->sum('amount');
            $totalHt = $expenseReport->expenses->sum(fn ($expense) => $amountHt($expense));
        @endphp

        <header class="treasury-section-header">
            <div class="treasury-report-summary">
                <p>
                    {{ __('messages.beneficiary') }}: <strong>{{ Auth::user()->name }}</strong>
                </p>
                <span class="status-chip treasury-status treasury-status--{{ $expenseReport->status }}">
                    {{ __('messages.expense_report_status_' . $expenseReport->status) }}
                </span>
            </div>
            <div class="treasury-report-toolbar">
                @if(Auth::user()->getMode() == "Edit" && $expenseReport->status === 'validated')
                    <form class="treasury-report-payment-form" method="post" action="{{ route('treasury.reports.pay', $expenseReport) }}" id="treasury-report-payment-form">
                        @csrf
                        <x-text-input class="treasury-report-payment-date" type="date" name="payment_date" value="{{ old('payment_date', now()->toDateString()) }}" aria-label="{{ __('messages.payment_date') }}" required />
                    </form>
                @endif
                <div class="treasury-report-actions">
                    <a class="icon icon--pdf-download" href="{{ route('treasury.reports.pdf', $expenseReport) }}" aria-label="{{ __('messages.download_pdf') }}" title="{{ __('messages.download_pdf') }}">
                        <x-rune-icon name="pdf-download" :size="18" />
                    </a>
                    @if(Auth::user()->getMode() == "Edit")
                        @if($expenseReport->status === 'draft' || $expenseReport->status === 'validated')
                        <form method="post" action="{{ route('treasury.reports.validate', $expenseReport) }}">
                            @csrf
                            <button class="icon {{ $expenseReport->status === 'validated' ? 'icon--validate-cancel' : 'icon--validate' }}" type="submit" aria-label="{{ $expenseReport->status === 'validated' ? __('messages.cancel_validation') : __('messages.validate') }}" title="{{ $expenseReport->status === 'validated' ? __('messages.cancel_validation') : __('messages.validate') }}">
                                <x-rune-icon :name="$expenseReport->status === 'validated' ? 'validate-cancel' : 'validate'" :size="18" />
                            </button>
                        </form>
                        @endif
                        @if($expenseReport->status === 'validated' || $expenseReport->status === 'paid')
                            @if($expenseReport->status === 'validated')
                                <button class="icon icon--payed" type="submit" form="treasury-report-payment-form" aria-label="{{ __('messages.mark_as_paid') }}" title="{{ __('messages.mark_as_paid') }}">
                                    <x-rune-icon name="payed" :size="20" />
                                </button>
                            @else
                                <form method="post" action="{{ route('treasury.reports.pay', $expenseReport) }}">
                                    @csrf
                                    <x-button-payed :paid="true" />
                                </form>
                            @endif
                        @endif
                        @if($expenseReport->status === 'draft')
                        <a class="icon icon--add" href="{{ route('treasury.expenses.create', ['report_id' => $expenseReport->id]) }}" aria-label="{{ __('messages.add_expense_to_report') }}" title="{{ __('messages.add_expense_to_report') }}">
                            <x-rune-icon name="add" :size="20" />
                        </a>
                        @endif
                    @endif
                </div>
            </div>
        </header>

        @if($expenseReport->expenses->isEmpty())
            <p class="treasury-empty"><x-empty-state icon="cart">{{ __('messages.no_expense_report') }}</x-empty-state></p>
        @else
            <div class="data-table">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('messages.date') }}</th>
                        <th>{{ __('messages.description') }}</th>
                        <th>{{ __('messages.vendor') }}</th>
                        <th>{{ __('messages.category') }}</th>
                        <th>{{ __('messages.amount_ht') }}</th>
                        <th>{{ __('messages.tax_amount') }}</th>
                        <th>{{ __('messages.amount_ttc') }}</th>
                        <th>{{ __('messages.recurring') }}</th>
                        @if(Auth::user()->getMode() == "Edit" && $expenseReport->status === 'draft')
                            <th>{{ __('messages.actions') }}</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($expenseReport->expenses as $expense)
                    <tr>
                        <td>@formatDate($expense->expense_date)</td>
                        <td>{{ $expense->label }}</td>
                        <td>{{ $expense->vendor }}</td>
                        <td>{{ $expense->category }}</td>
                        <td class="money">@money($amountHt($expense))</td>
                        <td class="money">{{ number_format($taxRate($expense), 2) }}%</td>
                        <td class="money">@money($expense->amount)</td>
                        <td>{{ $expense->is_recurring ? __('messages.yes') : __('messages.no') }}</td>
                        @if(Auth::user()->getMode() == "Edit" && $expenseReport->status === 'draft')
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
                <tfoot>
                    <tr>
                        <th colspan="4">{{ __('messages.total') }}</th>
                        <th class="money">@money($totalHt)</th>
                        <th></th>
                        <th class="money">@money($totalTtc)</th>
                        <th></th>
                        @if(Auth::user()->getMode() == "Edit" && $expenseReport->status === 'draft')
                            <th></th>
                        @endif
                    </tr>
                </tfoot>
            </table>
            </div>
        @endif
    </section>
</x-app-layout>
