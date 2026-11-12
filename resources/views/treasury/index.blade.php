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
</x-app-layout>
