<x-app-layout>
    <x-slot name="header">
        <h2 class="print:hidden">{{ __('messages.billing_preparation') }} @monthName($current_month) {{ $current_year }}</h2>
    </x-slot>

    <x-scheduling-module-tabs />

    <section class="planning-controls print:hidden">
        <x-period-selector :years="$years" :months="$months" :current_year="$current_year" :current_month="$current_month" route="billing" />
    </section>

    <section class="planning-calendar-container">
        @if ($monthly_hours == 0)
            <div class="alert alert-warning">
                {{ __('messages.no_hours_logged_this_month') }}
            </div>
        @else
            @foreach ($schools as $school => $courses)
                <article class="billing-school-box">
                    <header class="program-section-header">
                        <h2>{{ $school }}</h2>
                    </header>

                    @foreach ($courses['courses'] as $course_id => $schedules)
                        <section class="billing-course-box">
                            <h3 class="billing-course-title">{{ $schedules['course_name'] }}</h3>

                            <div class="data-table data-table--flat billing-sessions-table">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>{{ __('messages.group') }}</th>
                                            <th>{{ __('messages.schedule') }}</th>
                                            <th>{{ __('messages.hours') }}</th>
                                            <th>{{ __('messages.bill') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($schedules['schedule'] as $planning_id => $schedule)
                                            <tr>
                                                <td>{{ $schedule['group'] }}</td>
                                                <td class="date">
                                                    @if (Auth::user()->getMode() == 'Edit')
                                                        <a class="billing-schedule-link" href="{{ route('planning.edit', $planning_id, 'billing') }}">
                                                            {{ \Carbon\Carbon::parse($schedule['begin'])->format('d/m/Y H:i') }} – {{ \Carbon\Carbon::parse($schedule['end'])->format('H:i') }}
                                                        </a>
                                                    @else
                                                        {{ \Carbon\Carbon::parse($schedule['begin'])->format('d/m/Y H:i') }} – {{ \Carbon\Carbon::parse($schedule['end'])->format('H:i') }}
                                                    @endif
                                                </td>
                                                <td class="money">
                                                    <span class="billing-hours-badge {{ $schedule['duration'] != $schedules['duration'] ? 'billing-hours-badge--warning' : '' }}">
                                                        @if ($schedule['billable_rate'] != 1)
                                                            ({{ number_format($schedule['billable_rate'], 2) }})
                                                        @endif
                                                        {{ number_format($schedule['duration'], 1) }} h
                                                    </span>
                                                </td>
                                                <td>
                                                    @if ($schedule['bill'])
                                                        <span class="status-chip status-chip--bill">{{ $schedule['bill'] }}</span>
                                                    @else
                                                        <span class="group-table__empty">—</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="total-line billing-course-total">
                                <span>{{ __('messages.time_worked') }}: {{ number_format($schedules['hours'], 2) }} {{ __('messages.hours') }}</span>
                                <span>{{ __('messages.total') }}: {{ number_format($schedules['gain'], 2) }} € HT / {{ number_format($schedules['gain'] * 1.2, 2) }} € TTC</span>
                            </div>
                        </section>
                    @endforeach

                    <footer class="billing-school-footer">
                        <div class="billing-school-footer__totals">
                            <span>{{ __('messages.total_time_worked') }}: {{ number_format($courses['hours'], 2) }} {{ __('messages.hours') }}</span>
                            <span>{{ __('messages.school_total') }}: {{ number_format($courses['gain'], 2) }} € HT / {{ number_format($courses['gain'] * 1.2, 2) }} € TTC</span>
                        </div>

                        @if (Auth::user()->getMode() == 'Edit')
                            <div class="billing-school-footer__actions">
                                @if (isset($schedule['bill']) && $schedule['bill'] != '')
                                    <span class="billing-invoice-assigned">{{ __('messages.invoice_already_assigned') }}</span>
                                @else
                                    <form action="{{ route('billing.setBill') }}" class="billing-assign-form" method="post">
                                        @csrf
                                        <input type="hidden" name="school_id" value="{{ $courses['school_id'] }}">
                                        <input type="hidden" name="course_id" value="{{ $course_id }}">
                                        <input type="hidden" name="month" value="{{ $current_month }}">
                                        <input type="hidden" name="year" value="{{ $current_year }}">
                                        <label class="billing-assign-form__label" for="invoice_id_{{ $courses['school_id'] }}">{{ __('messages.assign') }}</label>
                                        <select name="invoice_id" id="invoice_id_{{ $courses['school_id'] }}" class="form-input billing-assign-form__select">
                                            @foreach ($bills as $bill)
                                                <option value="{{ $bill->id }}">{{ $bill->id }}</option>
                                            @endforeach
                                        </select>
                                        <x-button-secondary type="submit">{{ __('messages.save') }}</x-button-secondary>
                                    </form>
                                    <form action="{{ route('invoice.create') }}" class="billing-assign-form" method="get">
                                        @csrf
                                        <input type="hidden" name="school_id" value="{{ $courses['school_id'] }}">
                                        <input type="hidden" name="course_id" value="{{ $course_id }}">
                                        <input type="hidden" name="month" value="{{ $current_month }}">
                                        <input type="hidden" name="year" value="{{ $current_year }}">
                                        <input type="hidden" name="cmd" value="detailed">
                                        <x-text-input type="date" name="bill_date" value="{{ date('Y-m-d') }}" />
                                        <x-button-primary type="submit">{{ __('messages.create') }}</x-button-primary>
                                    </form>
                                @endif
                            </div>
                        @endif
                    </footer>
                </article>
            @endforeach
        @endif

        <div class="planning-summary">
            <div class="planning-summary-item">
                {{ __('messages.time_worked') }} = {{ number_format($monthly_hours, 2) }} {{ __('messages.hours') }}
            </div>
            <div class="planning-summary-item">
                {{ __('messages.monthly_gain') }} = {{ number_format($monthly_gain, 2) }} €
            </div>
            <div class="planning-summary-item">
                {{ __('messages.hour_rate') }} = @if ($monthly_hours > 0) {{ number_format($monthly_gain / $monthly_hours, 2) }} @else 0 @endif €
            </div>
        </div>
    </section>
</x-app-layout>
