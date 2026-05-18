<x-app-layout>
    <x-slot name="header">
        <h2>{{ __('messages.expense_report_detail') }} @monthName($expenseReport->month) {{ $expenseReport->year }}</h2>
    </x-slot>

    <section>
        @php
            $totalTax = $expenseReport->expenses->sum(fn ($expense) => $expense->tax_amount ?? 0);
            $totalTtc = $expenseReport->expenses->sum('amount');
            $totalHt = $totalTtc - $totalTax;
        @endphp

        <header class="treasury-section-header">
            <div>
                <p class="treasury-empty">
                    {{ __('messages.beneficiary') }}: {{ Auth::user()->name }} -
                    {{ __('messages.status') }}: {{ __('messages.expense_report_status_' . $expenseReport->status) }}
                </p>
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
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M7 3.75h6.25L18 8.5v11.75H7z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                            <path d="M13.25 3.75V8.5H18" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                            <path d="M12 11.25v5.25" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                            <path d="m9.75 14.5 2.25 2.25 2.25-2.25" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                    @if(Auth::user()->getMode() == "Edit")
                        @if($expenseReport->status === 'draft' || $expenseReport->status === 'validated')
                        <form method="post" action="{{ route('treasury.reports.validate', $expenseReport) }}">
                            @csrf
                            <button class="icon {{ $expenseReport->status === 'validated' ? 'icon--validate-cancel' : 'icon--validate' }}" type="submit" aria-label="{{ $expenseReport->status === 'validated' ? __('messages.cancel_validation') : __('messages.validate') }}" title="{{ $expenseReport->status === 'validated' ? __('messages.cancel_validation') : __('messages.validate') }}">
                                @if($expenseReport->status === 'validated')
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M6.5 8.5h8.25a4.75 4.75 0 1 1 0 9.5H9" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M9.25 5.75 6.5 8.5l2.75 2.75" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M11 13.5 13 15.5 16.75 11.75" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                @else
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M5 12.5 9.25 17 19 7" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                @endif
                            </button>
                        </form>
                        @endif
                        @if($expenseReport->status === 'validated' || $expenseReport->status === 'paid')
                            @if($expenseReport->status === 'validated')
                                <button class="icon icon--payed" type="submit" form="treasury-report-payment-form" aria-label="{{ __('messages.mark_as_paid') }}" title="{{ __('messages.mark_as_paid') }}">
                                    <img src="{{ asset('icons/payed.svg') }}" alt="" width="20" height="20" decoding="async">
                                </button>
                            @else
                                <form method="post" action="{{ route('treasury.reports.pay', $expenseReport) }}">
                                    @csrf
                                    <x-button-payed :paid="true" />
                                </form>
                            @endif
                        @endif
                        <a class="icon icon--add" href="{{ route('treasury.expenses.create', ['report_id' => $expenseReport->id]) }}" aria-label="{{ __('messages.add_expense_to_report') }}" title="{{ __('messages.add_expense_to_report') }}">
                            <img src="{{ asset('icons/add-circle-svgrepo-com.svg') }}" alt="" width="20" height="20" decoding="async">
                        </a>
                    @endif
                </div>
            </div>
        </header>

        @if($expenseReport->expenses->isEmpty())
            <p class="treasury-empty">{{ __('messages.no_expense_report') }}</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>{{ __('messages.date') }}</th>
                        <th>{{ __('messages.payment_date') }}</th>
                        <th>{{ __('messages.description') }}</th>
                        <th>{{ __('messages.vendor') }}</th>
                        <th>{{ __('messages.category') }}</th>
                        <th>{{ __('messages.amount_ht') }}</th>
                        <th>{{ __('messages.tax_amount') }}</th>
                        <th>{{ __('messages.amount_ttc') }}</th>
                        <th>{{ __('messages.recurring') }}</th>
                        @if(Auth::user()->getMode() == "Edit")
                            <th>{{ __('messages.actions') }}</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($expenseReport->expenses as $expense)
                    <tr>
                        <td>@formatDate($expense->expense_date)</td>
                        <td>@formatDate($expense->payment_date)</td>
                        <td>{{ $expense->label }}</td>
                        <td>{{ $expense->vendor }}</td>
                        <td>{{ $expense->category }}</td>
                        <td class="money">@money($expense->amount - ($expense->tax_amount ?? 0))</td>
                        <td class="money">@money($expense->tax_amount ?? 0)</td>
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
                <tfoot>
                    <tr>
                        <th colspan="5">{{ __('messages.total') }}</th>
                        <th class="money">@money($totalHt)</th>
                        <th class="money">@money($totalTax)</th>
                        <th class="money">@money($totalTtc)</th>
                        <th></th>
                        @if(Auth::user()->getMode() == "Edit")
                            <th></th>
                        @endif
                    </tr>
                </tfoot>
            </table>
        @endif
    </section>

    <section>
        <a class="btn btn-secondary" href="{{ route('treasury.index') }}">{{ __('messages.back') }}</a>
    </section>
</x-app-layout>
