<x-app-layout>
    <x-slot name="header">
        <h2>{{ __('messages.bank') }}</h2>
    </x-slot>

    <x-treasury-module-tabs active="bank" />

    @if (Auth::user()->getMode() == 'Edit')
        <dialog id="bank-add-dialog" class="bank-dialog" @if ($errors->has('name') && ! old('bank_edit_id')) open @endif>
            <form action="{{ route('treasury.bank.banks.store') }}" method="post" class="profile-modal-form nice-form">
                @csrf
                <h2 class="modal-title">{{ __('messages.bank_add') }}</h2>
                <div class="form-group">
                    <x-input-label for="bank_name">{{ __('messages.bank_name') }}</x-input-label>
                    <x-text-input id="bank_name" name="name" type="text" value="{{ old('name') }}" required />
                    <x-input-error :messages="$errors->get('name')" />
                </div>
                <div class="form-actions">
                    <x-button-secondary type="button" class="bank-dialog__close">{{ __('messages.cancel') }}</x-button-secondary>
                    <x-button-primary>{{ __('messages.bank_add') }}</x-button-primary>
                </div>
            </form>
        </dialog>

        @foreach ($banks as $bank)
            <dialog id="bank-edit-dialog-{{ $bank->id }}" class="bank-dialog" @if ($errors->has('name') && (int) old('bank_edit_id') === $bank->id) open @endif>
                <form action="{{ route('treasury.bank.banks.update', $bank) }}" method="post" class="profile-modal-form nice-form">
                    @csrf
                    @method('put')
                    <input type="hidden" name="bank_edit_id" value="{{ $bank->id }}" />
                    <h2 class="modal-title">{{ __('messages.bank_edit') }}</h2>
                    <div class="form-group">
                        <x-input-label :for="'bank_edit_name_'.$bank->id">{{ __('messages.bank_name') }}</x-input-label>
                        <x-text-input :id="'bank_edit_name_'.$bank->id" name="name" type="text" value="{{ old('name', $bank->name) }}" required />
                        <x-input-error :messages="$errors->get('name')" />
                    </div>
                    <div class="form-actions">
                        <x-button-secondary type="button" class="bank-dialog__close">{{ __('messages.cancel') }}</x-button-secondary>
                        <x-button-primary>{{ __('messages.save') }}</x-button-primary>
                    </div>
                    @if ($bank->accounts->isEmpty())
                        <div class="bank-manage-actions">
                            <form action="{{ route('treasury.bank.banks.destroy', $bank) }}" method="post" onsubmit="return confirm(@js(__('messages.delete_confirm_title')));">
                                @csrf
                                @method('delete')
                                <x-button-secondary type="submit" class="btn--compact">{{ __('messages.bank_delete') }}</x-button-secondary>
                            </form>
                        </div>
                    @else
                        <p class="form-hint">{{ __('messages.bank_delete_has_accounts') }}</p>
                    @endif
                </form>
            </dialog>
        @endforeach
    @endif

    <section class="bank-cards-section">
        <div class="bank-cards-grid">
            @foreach ($banks as $bank)
                @php
                    $isSelected = $selectedBank && $selectedBank->id === $bank->id;
                    $isBillingBank = $company->billingBankAccount && $company->billingBankAccount->bank_id === $bank->id;
                @endphp
                <article @class(['bank-card-wrap', 'bank-card-wrap--selected' => $isSelected])>
                    <a href="{{ route('treasury.bank.index', ['bank' => $bank->id]) }}" class="bank-card__body">
                        <span class="bank-card__name">{{ $bank->name }}</span>
                        <span class="bank-card__meta">
                            {{ trans_choice('messages.bank_account_count', $bank->accounts->count(), ['count' => $bank->accounts->count()]) }}
                        </span>
                        @if ($isBillingBank)
                            <span class="bank-card__badge">{{ __('messages.billing_bank_account') }}</span>
                        @endif
                    </a>
                    @if (Auth::user()->getMode() == 'Edit')
                        <div class="bank-card__actions">
                            <button
                                type="button"
                                class="icon icon--edit"
                                aria-label="{{ __('messages.bank_edit') }}"
                                data-bank-dialog-open="bank-edit-dialog-{{ $bank->id }}"
                            >
                                <img src="{{ asset('icons/edit.svg') }}" alt="" width="18" height="18" decoding="async">
                            </button>
                            @if ($bank->accounts->isEmpty())
                                <form action="{{ route('treasury.bank.banks.destroy', $bank) }}" method="post" onsubmit="return confirm(@js(__('messages.delete_confirm_title')));">
                                    @csrf
                                    @method('delete')
                                    <x-button-delete :label="__('messages.bank_delete')" />
                                </form>
                            @endif
                        </div>
                    @endif
                </article>
            @endforeach
            @if (Auth::user()->getMode() == 'Edit')
                <article class="bank-card-wrap bank-card-wrap--add">
                    <button
                        type="button"
                        class="icon icon--add"
                        aria-label="{{ __('messages.bank_add') }}"
                        title="{{ __('messages.bank_add') }}"
                        data-bank-dialog-open="bank-add-dialog"
                    >
                        <img src="{{ asset('icons/add-circle-svgrepo-com.svg') }}" alt="" width="20" height="20" decoding="async">
                    </button>
                </article>
            @endif
        </div>
    </section>

    @if ($selectedBank)
        <section class="bank-account-cards-section">
            <header class="treasury-section-header">
                <h3>{{ $selectedBank->name }} — {{ __('messages.bank_accounts') }}</h3>
            </header>

            @if ($selectedBank->accounts->isEmpty())
                <p class="form-hint">{{ __('messages.bank_no_accounts') }}</p>
            @else
                <div class="bank-account-cards-grid">
                    @foreach ($selectedBank->accounts as $account)
                        @php
                            $accountName = $account->label;
                        @endphp
                        <article class="bank-account-card">
                            <div class="bank-account-card__head">
                                <div class="bank-account-card__titles">
                                    <span class="bank-account-card__name">{{ $accountName ?: $account->account_number }}</span>
                                    @if ($accountName)
                                        <span class="bank-account-card__number">{{ $account->account_number }}</span>
                                    @endif
                                </div>
                                @if (Auth::user()->getMode() == 'Edit')
                                    <form action="{{ route('treasury.bank.accounts.destroy', $account) }}" method="post" class="bank-account-card__delete" onsubmit="return confirm(@js($account->imports_count > 0 ? __('messages.bank_account_delete_confirm') : __('messages.delete_confirm_title')));">
                                        @csrf
                                        @method('delete')
                                        <x-button-delete :label="__('messages.bank_account_delete')" />
                                    </form>
                                @endif
                            </div>
                            @if ($company->billing_bank_account_id === $account->id)
                                <span class="bank-card__badge">{{ __('messages.billing_bank_account') }}</span>
                            @endif
                            <p class="bank-account-card__opening">
                                <span class="bank-account-card__opening-label">{{ __('messages.opening_balance') }}</span>
                                @if ($account->opening_date)
                                    <span class="bank-account-card__opening-value">@formatDate($account->opening_date) · @money($account->opening_amount)</span>
                                @else
                                    <span class="bank-account-card__opening-value bank-account-card__opening-value--empty">—</span>
                                @endif
                            </p>
                            @if ($account->hasBillingDetails())
                                <span class="bank-account-card__meta">{{ $account->iban }}</span>
                            @endif
                            @if (Auth::user()->getMode() == 'Edit')
                                <details class="bank-account-edit bank-account-card__edit">
                                    <summary>{{ __('messages.bank_account_edit_opening') }}</summary>
                                    <form action="{{ route('treasury.bank.accounts.update', $account) }}" method="post" class="nice-form nice-form--embedded">
                                        @csrf
                                        @method('put')
                                        <div class="treasury-balance-form__fields">
                                            <div class="form-group">
                                                <x-input-label :for="'opening_date_'.$account->id">{{ __('messages.date') }}</x-input-label>
                                                <x-text-input :id="'opening_date_'.$account->id" type="date" name="opening_date" value="{{ $account->opening_date?->format('Y-m-d') }}" />
                                            </div>
                                            <div class="form-group">
                                                <x-input-label :for="'opening_amount_'.$account->id">{{ __('messages.amount') }}</x-input-label>
                                                <x-text-input :id="'opening_amount_'.$account->id" class="treasury-balance-input--amount" type="number" step="0.01" name="opening_amount" value="{{ $account->opening_amount }}" />
                                            </div>
                                        </div>
                                        <x-button-primary type="submit" class="btn--compact">{{ __('messages.save') }}</x-button-primary>
                                    </form>
                                </details>
                                <details class="bank-account-edit bank-account-card__edit">
                                    <summary>{{ __('messages.bank_account_edit_billing') }}</summary>
                                    <form action="{{ route('treasury.bank.accounts.update', $account) }}" method="post" class="nice-form nice-form--embedded">
                                        @csrf
                                        @method('put')
                                        <div class="form-group">
                                            <x-input-label :for="'label_'.$account->id">{{ __('messages.bank_account_label') }}</x-input-label>
                                            <x-text-input :id="'label_'.$account->id" name="label" type="text" value="{{ $account->label }}" />
                                        </div>
                                        <div class="form-group">
                                            <x-input-label :for="'iban_holder_'.$account->id">{{ __('messages.account_holder') }}</x-input-label>
                                            <x-text-input :id="'iban_holder_'.$account->id" name="iban_holder" type="text" value="{{ $account->iban_holder }}" />
                                        </div>
                                        <div class="bank-rib-fields">
                                            <div class="form-group">
                                                <x-input-label :for="'rib_bank_'.$account->id">{{ __('messages.bank_code') }}</x-input-label>
                                                <x-text-input :id="'rib_bank_'.$account->id" name="rib_bank_code" type="text" value="{{ $account->rib_bank_code }}" />
                                            </div>
                                            <div class="form-group">
                                                <x-input-label :for="'rib_branch_'.$account->id">{{ __('messages.branch_code') }}</x-input-label>
                                                <x-text-input :id="'rib_branch_'.$account->id" name="rib_branch_code" type="text" value="{{ $account->rib_branch_code }}" />
                                            </div>
                                            <div class="form-group">
                                                <x-input-label :for="'rib_account_'.$account->id">{{ __('messages.rib_account_number') }}</x-input-label>
                                                <x-text-input :id="'rib_account_'.$account->id" name="rib_account_number" type="text" value="{{ $account->rib_account_number }}" />
                                            </div>
                                            <div class="form-group">
                                                <x-input-label :for="'rib_key_'.$account->id">{{ __('messages.key') }}</x-input-label>
                                                <x-text-input :id="'rib_key_'.$account->id" name="rib_key" type="text" value="{{ $account->rib_key }}" />
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <x-input-label :for="'iban_'.$account->id">{{ __('messages.iban_code') }}</x-input-label>
                                            <x-text-input :id="'iban_'.$account->id" name="iban" type="text" value="{{ $account->iban }}" />
                                        </div>
                                        <div class="form-group">
                                            <x-input-label :for="'bic_'.$account->id">{{ __('messages.bic_code') }}</x-input-label>
                                            <x-text-input :id="'bic_'.$account->id" name="bic" type="text" value="{{ $account->bic }}" />
                                        </div>
                                        <x-button-primary type="submit" class="btn--compact">{{ __('messages.save') }}</x-button-primary>
                                    </form>
                                </details>
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        @if (Auth::user()->getMode() == 'Edit')
            <section class="school-panel" x-data="{ open: true }">
                <div class="school-panel__box">
                    <header class="school-panel__header">
                        <h3 class="school-panel__title">{{ __('messages.billing_bank_account') }}</h3>
                        <x-panel-toggle controls="bank-billing-panel" />
                    </header>
                    <div id="bank-billing-panel" x-show="open" x-transition>
                        <p class="form-hint">{{ __('messages.billing_bank_account_help') }}</p>
                        @if ($selectedBank->accounts->isEmpty())
                            <p class="treasury-empty">{{ __('messages.bank_account_required') }}</p>
                        @else
                            <form action="{{ route('treasury.bank.billing-account.update') }}" method="post" class="nice-form nice-form--embedded bank-billing-form">
                                @csrf
                                @method('put')
                                <input type="hidden" name="bank" value="{{ $selectedBank->id }}" />
                                <div class="form-group">
                                    <x-input-label for="billing_bank_account_id">{{ __('messages.bank_select_account') }}</x-input-label>
                                    <select name="billing_bank_account_id" id="billing_bank_account_id" class="form-input">
                                        <option value="">{{ __('messages.billing_bank_account_none_option') }}</option>
                                        @foreach ($selectedBank->accounts as $account)
                                            <option value="{{ $account->id }}" @selected(old('billing_bank_account_id', $company->billing_bank_account_id) == $account->id)>
                                                {{ $account->displayName() }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-actions">
                                    <x-button-primary class="btn--compact">{{ __('messages.save') }}</x-button-primary>
                                </div>
                            </form>
                            @if ($company->billingBankAccount && $company->billingBankAccount->bank_id === $selectedBank->id)
                                <div class="company-billing-preview">
                                    <x-company-billing-details :account="$company->billingBankAccount" />
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </section>

            @php
                $accountFormOpen = (int) old('bank_id') === $selectedBank->id
                    || $errors->has('account_number')
                    || $errors->has('label')
                    || $errors->has('opening_date')
                    || $errors->has('opening_amount');
            @endphp
            <section class="school-panel" x-data="{ open: {{ $accountFormOpen ? 'true' : 'false' }} }">
                <div class="school-panel__box">
                    <header class="school-panel__header">
                        <h3 class="school-panel__title">{{ __('messages.bank_account_add') }}</h3>
                        <x-panel-toggle controls="bank-account-panel" />
                    </header>
                    <div id="bank-account-panel" x-show="open" x-transition class="bank-setup">
                        <form action="{{ route('treasury.bank.accounts.store') }}" method="post" class="bank-setup__form nice-form nice-form--embedded">
                            @csrf
                            <input type="hidden" name="bank_id" value="{{ $selectedBank->id }}" />
                            <div class="form-group">
                                <x-input-label for="account_number">{{ __('messages.bank_account_number') }}</x-input-label>
                                <x-text-input id="account_number" name="account_number" type="text" value="{{ old('account_number') }}" required />
                                <x-input-error :messages="$errors->get('account_number')" />
                            </div>
                            <div class="form-group">
                                <x-input-label for="account_label">{{ __('messages.bank_account_label') }}</x-input-label>
                                <x-text-input id="account_label" name="label" type="text" value="{{ old('label') }}" />
                                <x-input-error :messages="$errors->get('label')" />
                            </div>
                            <div class="treasury-balance-form__fields">
                                <div class="form-group">
                                    <x-input-label for="account_opening_date">{{ __('messages.opening_balance') }}</x-input-label>
                                    <x-text-input type="date" name="opening_date" id="account_opening_date" value="{{ old('opening_date') }}" />
                                    <x-input-error :messages="$errors->get('opening_date')" />
                                </div>
                                <div class="form-group">
                                    <x-input-label for="account_opening_amount" class="sr-only">{{ __('messages.amount') }}</x-input-label>
                                    <x-text-input class="treasury-balance-input--amount" type="number" step="0.01" name="opening_amount" id="account_opening_amount" value="{{ old('opening_amount') }}" placeholder="0" />
                                    <x-input-error :messages="$errors->get('opening_amount')" />
                                </div>
                            </div>
                            <p class="form-hint">{{ __('messages.bank_account_billing_fields') }}</p>
                            <div class="form-group">
                                <x-input-label for="iban_holder">{{ __('messages.account_holder') }}</x-input-label>
                                <x-text-input id="iban_holder" name="iban_holder" type="text" value="{{ old('iban_holder') }}" />
                                <x-input-error :messages="$errors->get('iban_holder')" />
                            </div>
                            <div class="bank-rib-fields">
                                <div class="form-group">
                                    <x-input-label for="rib_bank_code">{{ __('messages.bank_code') }}</x-input-label>
                                    <x-text-input id="rib_bank_code" name="rib_bank_code" type="text" value="{{ old('rib_bank_code') }}" />
                                    <x-input-error :messages="$errors->get('rib_bank_code')" />
                                </div>
                                <div class="form-group">
                                    <x-input-label for="rib_branch_code">{{ __('messages.branch_code') }}</x-input-label>
                                    <x-text-input id="rib_branch_code" name="rib_branch_code" type="text" value="{{ old('rib_branch_code') }}" />
                                    <x-input-error :messages="$errors->get('rib_branch_code')" />
                                </div>
                                <div class="form-group">
                                    <x-input-label for="rib_account_number">{{ __('messages.rib_account_number') }}</x-input-label>
                                    <x-text-input id="rib_account_number" name="rib_account_number" type="text" value="{{ old('rib_account_number') }}" />
                                    <x-input-error :messages="$errors->get('rib_account_number')" />
                                </div>
                                <div class="form-group">
                                    <x-input-label for="rib_key">{{ __('messages.key') }}</x-input-label>
                                    <x-text-input id="rib_key" name="rib_key" type="text" value="{{ old('rib_key') }}" />
                                    <x-input-error :messages="$errors->get('rib_key')" />
                                </div>
                            </div>
                            <div class="form-group">
                                <x-input-label for="iban">{{ __('messages.iban_code') }}</x-input-label>
                                <x-text-input id="iban" name="iban" type="text" value="{{ old('iban') }}" />
                                <x-input-error :messages="$errors->get('iban')" />
                            </div>
                            <div class="form-group">
                                <x-input-label for="bic">{{ __('messages.bic_code') }}</x-input-label>
                                <x-text-input id="bic" name="bic" type="text" value="{{ old('bic') }}" />
                                <x-input-error :messages="$errors->get('bic')" />
                            </div>
                            <div class="form-actions">
                                <x-button-primary class="btn--compact">{{ __('messages.bank_account_add') }}</x-button-primary>
                            </div>
                        </form>
                    </div>
                </div>
            </section>

            <section class="school-panel" x-data="{ open: true }">
                <div class="school-panel__box">
                    <header class="school-panel__header">
                        <h3 class="school-panel__title">{{ __('messages.bank_import_title') }}</h3>
                        <x-panel-toggle controls="bank-import-panel" />
                    </header>
                    <div id="bank-import-panel" x-show="open" x-transition>
                        <p class="form-hint">{{ __('messages.bank_import_help') }}</p>
                        @if ($selectedBank->accounts->isNotEmpty())
                            <form action="{{ route('treasury.bank.import') }}" method="post" enctype="multipart/form-data" class="bank-import-form nice-form nice-form--embedded">
                                @csrf
                                <div class="form-group">
                                    <x-input-label for="bank_account_id">{{ __('messages.bank_select_account') }}</x-input-label>
                                    <select name="bank_account_id" id="bank_account_id" class="form-input" required>
                                        @foreach ($selectedBank->accounts as $account)
                                            <option value="{{ $account->id }}">{{ $account->displayName() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <x-input-label for="statement_file">{{ __('messages.bank_statement_file') }}</x-input-label>
                                    <input type="file" name="statement_file" id="statement_file" class="form-input" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required />
                                    <x-input-error :messages="$errors->get('statement_file')" />
                                </div>
                                <div class="form-actions">
                                    <x-button-primary>{{ __('messages.bank_import_action') }}</x-button-primary>
                                </div>
                            </form>
                        @else
                            <p class="treasury-empty">{{ __('messages.bank_account_required') }}</p>
                        @endif
                    </div>
                </div>
            </section>
        @endif
    @elseif ($banks->isNotEmpty())
        <p class="treasury-empty">{{ __('messages.bank_select_prompt') }}</p>
    @endif

    <section>
        <header class="treasury-section-header">
            <h3>{{ __('messages.bank_import_history') }}</h3>
            @if ($selectedBank)
                <span class="form-hint">{{ $selectedBank->name }}</span>
            @endif
        </header>

        @if ($imports->isEmpty())
            <p class="treasury-empty">{{ __('messages.bank_import_empty') }}</p>
        @else
            <div class="data-table">
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('messages.date') }}</th>
                            <th>{{ __('messages.bank') }}</th>
                            <th>{{ __('messages.account') }}</th>
                            <th>{{ __('messages.file') }}</th>
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
                                <td>{{ $import->bankAccount?->bank?->name ?? '—' }}</td>
                                <td>{{ $import->bankAccount?->account_number ?? $import->account_number ?? '—' }}</td>
                                <td>{{ $import->file_name }}</td>
                                <td class="date">
                                    @if ($import->period_start && $import->period_end)
                                        @formatDate($import->period_start) – @formatDate($import->period_end)
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $import->lines_count }}</td>
                                <td>{{ $import->reconciled_lines_count }} / {{ $import->lines_count }}</td>
                                <td class="bank-import-actions">
                                    <a class="btn btn-secondary btn--compact" href="{{ route('treasury.bank.imports.show', $import) }}">{{ __('messages.open') }}</a>
                                    @if (Auth::user()->getMode() == 'Edit')
                                        <form action="{{ route('treasury.bank.imports.destroy', $import) }}" method="post" onsubmit="return confirm(@js(__('messages.bank_import_delete_confirm')));">
                                            @csrf
                                            @method('delete')
                                            <x-button-secondary type="submit" class="btn--compact">{{ __('messages.delete') }}</x-button-secondary>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    @if (Auth::user()->getMode() == 'Edit')
        <script>
            document.querySelectorAll('[data-bank-dialog-open]').forEach((trigger) => {
                trigger.addEventListener('click', (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                    const dialog = document.getElementById(trigger.dataset.bankDialogOpen);
                    dialog?.showModal();
                });
            });

            document.querySelectorAll('.bank-dialog').forEach((dialog) => {
                dialog.querySelectorAll('.bank-dialog__close').forEach((button) => {
                    button.addEventListener('click', () => dialog.close());
                });
                dialog.addEventListener('click', (event) => {
                    if (event.target === dialog) {
                        dialog.close();
                    }
                });
            });
        </script>
    @endif
</x-app-layout>
