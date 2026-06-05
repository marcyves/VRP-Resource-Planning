@php
    $reconcilableLabel = function ($reconciliation) {
        $item = $reconciliation->reconcilable;
        if (! $item) {
            return '—';
        }
        if ($item instanceof \App\Models\Invoice) {
            return __('messages.bank_match_invoice', ['id' => $item->id, 'amount' => number_format($item->amountTtc(), 2, ',', ' ')]);
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

    $lineReconciliationLabel = function (\App\Models\BankStatementLine $line) use ($reconcilableLabel) {
        $reconciliations = $line->reconciliations;

        if ($reconciliations->isEmpty()) {
            return null;
        }

        $invoices = $reconciliations
            ->filter(fn ($r) => $r->reconcilable instanceof \App\Models\Invoice)
            ->map(fn ($r) => $r->reconcilable);

        if ($invoices->count() === $reconciliations->count() && $invoices->count() > 1) {
            $school = $invoices->first()?->school;

            return __('messages.bank_match_invoices_label', [
                'count' => $invoices->count(),
                'client' => $school?->name ?? '—',
            ]);
        }

        return $reconciliations
            ->map(fn ($r) => $reconcilableLabel($r))
            ->implode(' · ');
    };

    $sortLink = function (string $column) use ($import, $sort, $direction, $reconciledFilter) {
        $nextDirection = ($sort === $column && $direction === 'asc') ? 'desc' : 'asc';

        return route('treasury.bank.imports.show', array_filter([
            'import' => $import,
            'sort' => $column,
            'direction' => $nextDirection,
            'reconciled' => $reconciledFilter !== 'all' ? $reconciledFilter : null,
        ]));
    };
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2>{{ __('messages.bank') }} — {{ $import->bankAccount?->displayName() ?? $import->file_name }}</h2>
    </x-slot>

    <x-treasury-module-tabs active="bank" />

    <section class="bank-reconciliation-meta">
        <p>
            @if ($import->bankAccount?->bank)
                <strong>{{ $import->bankAccount->bank->name }}</strong>
            @endif
            @if ($import->bankAccount?->account_number ?? $import->account_number)
                · {{ __('messages.account') }} {{ $import->bankAccount?->account_number ?? $import->account_number }}
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
        <p class="bank-reconciliation-meta__actions">
            <a class="btn btn-secondary btn--compact" href="{{ route('treasury.bank.index', array_filter(['bank' => $import->bankAccount?->bank_id])) }}">{{ __('messages.back_to_imports') }}</a>
            @if (Auth::user()->getMode() == 'Edit')
                <form action="{{ route('treasury.bank.imports.destroy', $import) }}" method="post" onsubmit="return confirm(@js(__('messages.bank_import_delete_confirm')));">
                    @csrf
                    @method('delete')
                    <x-button-secondary type="submit" class="btn--compact">{{ __('messages.bank_import_delete') }}</x-button-secondary>
                </form>
            @endif
        </p>
    </section>

    <section>
        <form method="get" action="{{ route('treasury.bank.imports.show', $import) }}" class="bank-reconciliation-toolbar nice-form nice-form--embedded">
            <input type="hidden" name="sort" value="{{ $sort }}" />
            <input type="hidden" name="direction" value="{{ $direction }}" />
            <div class="form-group">
                <x-input-label for="reconciled_filter">{{ __('messages.bank_reconciliation_filter') }}</x-input-label>
                <select name="reconciled" id="reconciled_filter" class="form-input" onchange="this.form.submit()">
                    <option value="all" @selected($reconciledFilter === 'all')>{{ __('messages.bank_reconciliation_filter_all') }}</option>
                    <option value="reconciled" @selected($reconciledFilter === 'reconciled')>{{ __('messages.bank_reconciliation_filter_reconciled') }}</option>
                    <option value="unreconciled" @selected($reconciledFilter === 'unreconciled')>{{ __('messages.bank_reconciliation_filter_unreconciled') }}</option>
                </select>
            </div>
            <p class="form-hint bank-reconciliation-toolbar__count">
                {{ trans_choice('messages.bank_reconciliation_lines_shown', $lines->count(), ['count' => $lines->count(), 'total' => $import->lines_count]) }}
            </p>
        </form>

        <div class="data-table bank-reconciliation-table">
            <table>
                <thead>
                    <tr>
                        <th class="bank-reconciliation-table__sortable">
                            <a href="{{ $sortLink('date') }}" @class(['bank-reconciliation-table__sort-link', 'bank-reconciliation-table__sort-link--active' => $sort === 'date'])">
                                {{ __('messages.date') }}
                                @if ($sort === 'date')
                                    <span class="bank-reconciliation-table__sort-indicator" aria-hidden="true">{{ $direction === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </a>
                        </th>
                        <th class="bank-reconciliation-table__sortable">
                            <a href="{{ $sortLink('label') }}" @class(['bank-reconciliation-table__sort-link', 'bank-reconciliation-table__sort-link--active' => $sort === 'label'])">
                                {{ __('messages.label') }}
                                @if ($sort === 'label')
                                    <span class="bank-reconciliation-table__sort-indicator" aria-hidden="true">{{ $direction === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </a>
                        </th>
                        <th class="money">{{ __('messages.debit') }}</th>
                        <th class="money">{{ __('messages.credit') }}</th>
                        <th>{{ __('messages.reconciliation') }}</th>
                        @if (Auth::user()->getMode() == 'Edit')
                            <th>{{ __('messages.actions') }}</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse ($lines as $line)
                        <tr @class(['bank-line--reconciled' => $line->isReconciled()])>
                            <td class="date">@formatDate($line->operation_date)</td>
                            <td class="bank-line__label">{{ $line->label }}</td>
                            <td class="money">@if($line->debit > 0)@money($line->debit)@endif</td>
                            <td class="money">@if($line->credit > 0)@money($line->credit)@endif</td>
                            <td>
                                @if ($line->isReconciled())
                                    <span class="status-chip treasury-status treasury-status--paid">{{ $lineReconciliationLabel($line) }}</span>
                                    @if ($line->reconciliations->count() > 1)
                                        <ul class="bank-line__reconciliation-details">
                                            @foreach ($line->reconciliations as $reconciliation)
                                                <li>{{ $reconcilableLabel($reconciliation) }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                @else
                                    <span class="status-chip treasury-status treasury-status--draft">{{ __('messages.unreconciled') }}</span>
                                @endif
                            </td>
                            @if (Auth::user()->getMode() == 'Edit')
                                <td class="bank-line__actions">
                                    @if ($line->isReconciled())
                                        @php $firstReconciliation = $line->reconciliations->first(); @endphp
                                        @if ($firstReconciliation)
                                            <form action="{{ route('treasury.bank.imports.unmatch', [$import, $firstReconciliation]) }}" method="post">
                                                @csrf
                                                @method('delete')
                                                <x-button-secondary type="submit" class="btn--compact">{{ __('messages.unmatch') }}</x-button-secondary>
                                            </form>
                                        @endif
                                    @else
                                        @php $candidates = $suggestions[$line->id] ?? ['invoices' => collect(), 'invoice_groups' => collect(), 'expenses' => collect(), 'expense_reports' => collect()]; @endphp
                                        <form action="{{ route('treasury.bank.imports.match', [$import, $line]) }}" method="post" class="bank-match-form">
                                            @csrf
                                            <select name="match_ref" class="form-input bank-match-form__select" required>
                                                <option value="">{{ __('messages.bank_match_choose_item') }}</option>
                                                @if ($line->isCredit() && ($candidates['invoice_groups'] ?? collect())->isNotEmpty())
                                                    <optgroup label="{{ __('messages.bank_match_invoice_groups') }}">
                                                        @foreach ($candidates['invoice_groups'] as $group)
                                                            <option value="invoices:{{ $group->invoices->pluck('id')->implode(',') }}">
                                                                {{ __('messages.bank_match_invoice_group_option', [
                                                                    'client' => $group->school?->name ?? '—',
                                                                    'count' => $group->invoices->count(),
                                                                    'amount' => number_format($group->total, 2, ',', ' '),
                                                                ]) }}
                                                                — {{ $group->invoices->pluck('id')->implode(', ') }}
                                                            </option>
                                                        @endforeach
                                                    </optgroup>
                                                @endif
                                                @if ($line->isCredit() && $candidates['invoices']->isNotEmpty())
                                                    <optgroup label="{{ __('messages.invoices') }}">
                                                        @foreach ($candidates['invoices'] as $invoice)
                                                            <option value="invoice:{{ $invoice->id }}">
                                                                {{ $invoice->id }} — @money($invoice->amountTtc()) {{ __('messages.amount_ttc') }}
                                                                @if ($invoice->school)
                                                                    ({{ $invoice->school->name }})
                                                                @endif
                                                                ({{ $invoice->bill_date ? \Carbon\Carbon::parse($invoice->bill_date)->format('d/m/Y') : '—' }})
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
                    @empty
                        <tr>
                            <td colspan="{{ Auth::user()->getMode() == 'Edit' ? 6 : 5 }}" class="treasury-empty">
                                {{ __('messages.bank_reconciliation_no_lines') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-app-layout>
