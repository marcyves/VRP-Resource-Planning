@php
    $reconcilableLabel = function ($reconciliation) {
        $item = $reconciliation->reconcilable;
        if (! $item) {
            return '—';
        }
        if ($item instanceof \App\Models\Invoice) {
            return __('messages.bank_match_invoice', ['id' => $item->id, 'amount' => number_format((float) $item->amount, 2, ',', ' ')]);
        }
        if ($item instanceof \App\Models\Expense) {
            return __('messages.bank_match_expense', ['label' => $item->label, 'amount' => number_format((float) $item->amount, 2, ',', ' ')]);
        }
        if ($item instanceof \App\Models\ExpenseReport) {
            $item->loadMissing('expenses');

            return __('messages.bank_match_expense_report', [
                'period' => sprintf('%02d/%d', $item->month, $item->year),
                'amount' => number_format((float) $item->expenses->sum('amount'), 2, ',', ' '),
            ]);
        }

        return '—';
    };
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2>{{ __('messages.bank_reconciliation') }} — {{ $import->file_name }}</h2>
    </x-slot>

    <x-treasury-module-tabs active="reconciliation" />

    <section class="bank-reconciliation-meta">
        <p>
            @if ($import->account_label)
                <strong>{{ $import->account_label }}</strong>
            @endif
            @if ($import->account_number)
                · {{ __('messages.account') }} {{ $import->account_number }}
            @endif
        </p>
        @if ($import->period_start && $import->period_end)
            <p class="form-hint">{{ __('messages.period') }} : @formatDate($import->period_start) – @formatDate($import->period_end)</p>
        @endif
        <p class="form-hint">
            {{ __('messages.bank_reconciliation_progress', [
                'done' => $import->reconciledCount(),
                'total' => $import->lines_count,
            ]) }}
        </p>
        <p>
            <a class="btn btn-secondary btn--compact" href="{{ route('treasury.reconciliation.index') }}">{{ __('messages.back_to_imports') }}</a>
        </p>
    </section>

    <section>
        <div class="data-table bank-reconciliation-table">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('messages.date') }}</th>
                        <th>{{ __('messages.label') }}</th>
                        <th class="money">{{ __('messages.debit') }}</th>
                        <th class="money">{{ __('messages.credit') }}</th>
                        <th>{{ __('messages.reconciliation') }}</th>
                        @if (Auth::user()->getMode() == 'Edit')
                            <th>{{ __('messages.actions') }}</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach ($lines as $line)
                        <tr @class(['bank-line--reconciled' => $line->isReconciled()])>
                            <td class="date">@formatDate($line->operation_date)</td>
                            <td class="bank-line__label">{{ $line->label }}</td>
                            <td class="money">@if($line->debit > 0)@money($line->debit)@endif</td>
                            <td class="money">@if($line->credit > 0)@money($line->credit)@endif</td>
                            <td>
                                @if ($line->reconciliation)
                                    <span class="status-chip treasury-status treasury-status--paid">{{ $reconcilableLabel($line->reconciliation) }}</span>
                                @else
                                    <span class="status-chip treasury-status treasury-status--draft">{{ __('messages.unreconciled') }}</span>
                                @endif
                            </td>
                            @if (Auth::user()->getMode() == 'Edit')
                                <td class="bank-line__actions">
                                    @if ($line->reconciliation)
                                        <form action="{{ route('treasury.reconciliation.unmatch', [$import, $line->reconciliation]) }}" method="post">
                                            @csrf
                                            @method('delete')
                                            <x-button-secondary type="submit" class="btn--compact">{{ __('messages.unmatch') }}</x-button-secondary>
                                        </form>
                                    @else
                                        @php $candidates = $suggestions[$line->id] ?? ['invoices' => collect(), 'expenses' => collect(), 'expense_reports' => collect()]; @endphp
                                        <form action="{{ route('treasury.reconciliation.match', [$import, $line]) }}" method="post" class="bank-match-form">
                                            @csrf
                                            <select name="match_ref" class="form-input bank-match-form__select" required>
                                                <option value="">{{ __('messages.bank_match_choose_item') }}</option>
                                                @if ($line->isCredit() && $candidates['invoices']->isNotEmpty())
                                                    <optgroup label="{{ __('messages.invoices') }}">
                                                        @foreach ($candidates['invoices'] as $invoice)
                                                            <option value="invoice:{{ $invoice->id }}">
                                                                {{ $invoice->id }} — @money($invoice->amount) ({{ $invoice->bill_date ? \Carbon\Carbon::parse($invoice->bill_date)->format('d/m/Y') : '—' }})
                                                            </option>
                                                        @endforeach
                                                    </optgroup>
                                                @endif
                                                @if ($line->isDebit() && $candidates['expenses']->isNotEmpty())
                                                    <optgroup label="{{ __('messages.standalone_expenses') }}">
                                                        @foreach ($candidates['expenses'] as $expense)
                                                            <option value="expense:{{ $expense->id }}">
                                                                {{ $expense->label }} — @money($expense->amount)
                                                            </option>
                                                        @endforeach
                                                    </optgroup>
                                                @endif
                                                @if ($line->isDebit() && $candidates['expense_reports']->isNotEmpty())
                                                    <optgroup label="{{ __('messages.expense_reports') }}">
                                                        @foreach ($candidates['expense_reports'] as $report)
                                                            <option value="expense_report:{{ $report->id }}">
                                                                @monthName($report->month) {{ $report->year }} — @money($report->expenses_sum_amount ?? 0)
                                                            </option>
                                                        @endforeach
                                                    </optgroup>
                                                @endif
                                            </select>
                                            <x-button-primary type="submit" class="btn--compact">{{ __('messages.match') }}</x-button-primary>
                                        </form>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</x-app-layout>
