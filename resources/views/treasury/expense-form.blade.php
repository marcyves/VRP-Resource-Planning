<x-app-layout>
    <x-slot name="header">
        <h2>{{ $expense->exists ? __('messages.expense_edit') : __('messages.expense_create') }}</h2>
    </x-slot>

    <section>
        <form class="group-form treasury-expense-form" method="post" action="{{ $expense->exists ? route('treasury.expenses.update', $expense) : route('treasury.expenses.store') }}">
            @csrf
            @if($expense->exists)
                @method('put')
            @endif
            @if($expense->expense_report_id)
                <input type="hidden" name="expense_report_id" value="{{ $expense->expense_report_id }}">
            @endif

            <div class="form-group">
                <x-input-label for="expense_date" :value="__('messages.date')" />
                <x-text-input id="expense_date" name="expense_date" type="date" :value="old('expense_date', $expense->expense_date)" required />
                <x-input-error :messages="$errors->get('expense_date')" />
            </div>

            @if(request('standalone') || ($expense->exists && !$expense->expense_report_id))
                <div class="form-group">
                    <x-input-label for="payment_date" :value="__('messages.payment_date')" />
                    <x-text-input id="payment_date" name="payment_date" type="date" :value="old('payment_date', $expense->payment_date)" />
                    <x-input-error :messages="$errors->get('payment_date')" />
                </div>
            @endif

            <div class="form-group">
                <x-input-label for="label" :value="__('messages.description')" />
                <x-text-input id="label" name="label" type="text" :value="old('label', $expense->label)" required />
                <x-input-error :messages="$errors->get('label')" />
            </div>

            <div class="form-group">
                <x-input-label for="vendor" :value="__('messages.vendor')" />
                <x-text-input id="vendor" name="vendor" type="text" list="expense-vendors" :value="old('vendor', $expense->vendor)" />
                <datalist id="expense-vendors">
                    @foreach($options['vendors'] as $vendor)
                    <option value="{{ $vendor }}"></option>
                    @endforeach
                </datalist>
            </div>

            <div class="form-group">
                <x-input-label for="amount" :value="__('messages.amount')" />
                <x-text-input id="amount" name="amount" type="number" step="0.01" min="0" :value="old('amount', $expense->amount)" required />
                <x-input-error :messages="$errors->get('amount')" />
            </div>

            <div class="form-group">
                <x-input-label for="tax_amount" :value="__('messages.tax_amount')" />
                <x-text-input id="tax_amount" name="tax_amount" type="number" step="0.01" min="0" max="100" :value="old('tax_amount', $expense->tax_amount ?? 20)" />
            </div>

            <div class="form-group">
                <x-input-label for="category" :value="__('messages.category')" />
                <x-text-input id="category" name="category" type="text" list="expense-categories" :value="old('category', $expense->category)" />
                <datalist id="expense-categories">
                    @foreach($options['categories'] as $category)
                    <option value="{{ $category }}"></option>
                    @endforeach
                </datalist>
            </div>

            <div class="form-group">
                <x-input-label for="payment_method" :value="__('messages.payment_method')" />
                <x-text-input id="payment_method" name="payment_method" type="text" :value="old('payment_method', $expense->payment_method)" />
            </div>

            <label class="treasury-checkbox">
                <input type="checkbox" name="include_in_expense_report" value="1" @checked(old('include_in_expense_report', (bool) $expense->expense_report_id || !request('standalone')))>
                {{ __('messages.include_in_expense_report') }}
            </label>

            <label class="treasury-checkbox">
                <input type="checkbox" name="is_recurring" value="1" @checked(old('is_recurring', $expense->is_recurring))>
                {{ __('messages.recurring_expense') }}
            </label>

            <div class="form-group">
                <x-input-label for="recurring_frequency" :value="__('messages.recurring_frequency')" />
                <select id="recurring_frequency" name="recurring_frequency" class="form-input">
                    <option value="">{{ __('messages.none') }}</option>
                    <option value="monthly" @selected(old('recurring_frequency', $expense->recurring_frequency) === 'monthly')>{{ __('messages.monthly') }}</option>
                    <option value="yearly" @selected(old('recurring_frequency', $expense->recurring_frequency) === 'yearly')>{{ __('messages.yearly') }}</option>
                </select>
                <x-input-error :messages="$errors->get('recurring_frequency')" />
            </div>

            <div class="form-group">
                <x-input-label for="recurring_until" :value="__('messages.recurring_until')" />
                <x-text-input id="recurring_until" name="recurring_until" type="date" :value="old('recurring_until', $expense->recurring_until)" />
                <x-input-error :messages="$errors->get('recurring_until')" />
            </div>

            <div class="form-actions">
                <x-button-primary>{{ __('messages.save') }}</x-button-primary>
                <a class="btn btn-secondary" href="{{ route('treasury.index') }}">{{ __('messages.cancel') }}</a>
            </div>
        </form>
    </section>
</x-app-layout>
