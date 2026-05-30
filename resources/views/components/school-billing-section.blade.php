@props([
    'school',
    'billingData' => null,
    'monthlyHours' => 0,
    'monthlyGain' => 0,
    'currentYear',
    'currentMonth',
    'months',
    'years',
    'bills',
    'byDate' => false,
    'hasPreviousUnbilled' => false,
])

<section id="billing" class="school-billing-section">
    <h3 class="school-section-header">{{ __('messages.billing_preparation') }} @monthName($currentMonth) {{ $currentYear }}</h3>

    <div class="planning-controls print:hidden">
        <x-period-selector
            :years="$years"
            :months="$months"
            :current_year="$currentYear"
            :current_month="$currentMonth"
            route="school.billing"
            :school="$school"
            :by_date="$byDate"
        />

        @if ($hasPreviousUnbilled)
            <a href="{{ route('school.billing.jumpUnbilled', $school) }}" class="btn btn-secondary billing-jump-unbilled">
                {{ __('messages.jump_to_unbilled_sessions') }}
            </a>
        @else
            <button type="button" class="btn btn-secondary billing-jump-unbilled" disabled>
                {{ __('messages.jump_to_unbilled_sessions') }}
            </button>
        @endif
    </div>

    @if (! $billingData)
        <div class="alert alert-warning">
            {{ __('messages.no_hours_logged_this_month') }}
        </div>
    @else
        @foreach ($billingData['courses'] as $courseId => $schedules)
            <section class="billing-course-box">
                <h4 class="billing-course-title">{{ $schedules['course_name'] }}</h4>

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
                            @foreach ($schedules['schedule'] as $planningId => $schedule)
                                <tr>
                                    <td>{{ $schedule['group'] }}</td>
                                    <td class="date">
                                        @if (Auth::user()->getMode() == 'Edit')
                                            <a class="billing-schedule-link" href="{{ route('planning.edit', $planningId) }}">
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
                <span>{{ __('messages.total_time_worked') }}: {{ number_format($billingData['hours'], 2) }} {{ __('messages.hours') }}</span>
                <span>{{ __('messages.school_total') }}: {{ number_format($billingData['gain'], 2) }} € HT / {{ number_format($billingData['gain'] * 1.2, 2) }} € TTC</span>
            </div>

            @if (Auth::user()->getMode() == 'Edit')
                <div class="billing-school-footer__actions">
                    @php
                        $lastSchedule = null;
                        foreach ($billingData['courses'] as $courseSchedules) {
                            foreach ($courseSchedules['schedule'] as $schedule) {
                                $lastSchedule = $schedule;
                            }
                        }
                    @endphp
                    @if ($lastSchedule && ($lastSchedule['bill'] ?? '') != '')
                        <span class="billing-invoice-assigned">{{ __('messages.invoice_already_assigned') }}</span>
                    @else
                        @php $courseId = array_key_first($billingData['courses']); @endphp
                        <form action="{{ route('school.billing.setBill', $school) }}" class="billing-assign-form" method="post">
                            @csrf
                            <input type="hidden" name="course_id" value="{{ $courseId }}">
                            <input type="hidden" name="month" value="{{ $currentMonth }}">
                            <input type="hidden" name="year" value="{{ $currentYear }}">
                            <label class="billing-assign-form__label" for="invoice_id_{{ $school->id }}">{{ __('messages.assign') }}</label>
                            <select name="invoice_id" id="invoice_id_{{ $school->id }}" class="form-input billing-assign-form__select">
                                @foreach ($bills as $bill)
                                    <option value="{{ $bill->id }}">{{ $bill->id }}</option>
                                @endforeach
                            </select>
                            <x-button-secondary type="submit">{{ __('messages.save') }}</x-button-secondary>
                        </form>
                        <form action="{{ route('invoice.create') }}" class="billing-assign-form" method="get">
                            @csrf
                            <input type="hidden" name="school_id" value="{{ $school->id }}">
                            <input type="hidden" name="course_id" value="{{ $courseId }}">
                            <input type="hidden" name="month" value="{{ $currentMonth }}">
                            <input type="hidden" name="year" value="{{ $currentYear }}">
                            <input type="hidden" name="cmd" value="detailed">
                            <x-text-input type="date" name="bill_date" value="{{ date('Y-m-d') }}" />
                            <x-button-primary type="submit">{{ __('messages.create') }}</x-button-primary>
                        </form>
                    @endif
                </div>
            @endif
        </footer>

        <div class="planning-summary">
            <div class="planning-summary-item">
                {{ __('messages.time_worked') }} = {{ number_format($monthlyHours, 2) }} {{ __('messages.hours') }}
            </div>
            <div class="planning-summary-item">
                {{ __('messages.monthly_gain') }} = {{ number_format($monthlyGain, 2) }} €
            </div>
            <div class="planning-summary-item">
                {{ __('messages.hour_rate') }} = @if ($monthlyHours > 0) {{ number_format($monthlyGain / $monthlyHours, 2) }} @else 0 @endif €
            </div>
        </div>
    @endif
</section>
