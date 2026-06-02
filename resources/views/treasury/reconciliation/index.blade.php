<x-app-layout>
    <x-slot name="header">
        <h2>{{ __('messages.bank_reconciliation') }}</h2>
    </x-slot>

    <x-treasury-module-tabs active="reconciliation" />

    @if (Auth::user()->getMode() == 'Edit')
    <section class="school-panel" x-data="{ open: true }">
        <div class="school-panel__box">
            <header class="school-panel__header">
                <h3 class="school-panel__title">{{ __('messages.bank_import_title') }}</h3>
                <x-panel-toggle controls="bank-import-panel" />
            </header>
            <div id="bank-import-panel" x-show="open" x-transition>
                <p class="form-hint">{{ __('messages.bank_import_help') }}</p>
                <form action="{{ route('treasury.reconciliation.import') }}" method="post" enctype="multipart/form-data" class="bank-import-form nice-form nice-form--embedded">
                    @csrf
                    <div class="form-group">
                        <x-input-label for="statement_file">{{ __('messages.bank_statement_file') }}</x-input-label>
                        <input type="file" name="statement_file" id="statement_file" class="form-input" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required />
                        <x-input-error :messages="$errors->get('statement_file')" />
                    </div>
                    <div class="form-actions">
                        <x-button-primary>{{ __('messages.bank_import_action') }}</x-button-primary>
                    </div>
                </form>
            </div>
        </div>
    </section>
    @endif

    <section>
        <header class="treasury-section-header">
            <h3>{{ __('messages.bank_import_history') }}</h3>
        </header>

        @if ($imports->isEmpty())
            <p class="treasury-empty">{{ __('messages.bank_import_empty') }}</p>
        @else
            <div class="data-table">
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('messages.date') }}</th>
                            <th>{{ __('messages.file') }}</th>
                            <th>{{ __('messages.account') }}</th>
                            <th>{{ __('messages.period') }}</th>
                            <th>{{ __('messages.operations') }}</th>
                            <th>{{ __('messages.reconciled') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($imports as $import)
                            <tr>
                                <td class="date">@formatDate($import->created_at)</td>
                                <td>{{ $import->file_name }}</td>
                                <td>{{ $import->account_number ?? '—' }}</td>
                                <td class="date">
                                    @if ($import->period_start && $import->period_end)
                                        @formatDate($import->period_start) – @formatDate($import->period_end)
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $import->lines_count }}</td>
                                <td>{{ $import->reconciled_lines_count }} / {{ $import->lines_count }}</td>
                                <td>
                                    <a class="btn btn-secondary btn--compact" href="{{ route('treasury.reconciliation.show', $import) }}">{{ __('messages.open') }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</x-app-layout>
