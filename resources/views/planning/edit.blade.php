<x-app-layout>
<x-slot name="header">
        <h2>{{ __('messages.group_planning') }}</h2>
    </x-slot>

    <x-scheduling-module-tabs />

    @php
    $begin_date = explode(" ", $planning->begin)[0];
    $begin_day = explode("-", $begin_date)[2];
    $begin_month = explode("-", $begin_date)[1];
    $begin_year = explode("-", $begin_date)[0];

    $begin_time = explode(" ", $planning->begin)[1];
    $begin_hour = explode(":", $begin_time)[0];
    $begin_minutes = explode(":", $begin_time)[1];

    $end_time = explode(" ", $planning->end)[1];
    $end_hour = explode(":", $end_time)[0];
    $end_minutes = explode(":", $end_time)[1];
    $session_locked = (bool) $planning->invoice_id;

    $formatDuration = function (int $minutes): string {
        if ($minutes <= 0) {
            return '—';
        }

        $hours = intdiv($minutes, 60);
        $mins = $minutes % 60;

        if ($hours > 0 && $mins > 0) {
            return $hours.' '.__('messages.hours_initial').' '.$mins;
        }

        if ($hours > 0) {
            return $hours.' '.__('messages.hours_initial');
        }

        return $mins.' min';
    };

    $formatMoney = function (float $amount): string {
        return number_format($amount, 2, ',', ' ').' €';
    };

    $formatRate = function (float $rate): string {
        return number_format($rate, 2, ',', ' ').' €/h';
    };

    $initialDurationLabel = $formatDuration(
        \Carbon\Carbon::parse($planning->begin)->diffInMinutes(\Carbon\Carbon::parse($planning->end))
    );
    $initialHourlyRateLabel = $formatRate((float) $hourlyRate);
    $initialBilledAmountLabel = $formatMoney((float) $billedAmount);

    $duplicateLabel = \Carbon\Carbon::parse($planning->begin)->format('d/m/Y H:i');
    $duplicateDefaultDate = \Carbon\Carbon::parse($planning->begin)->addDay()->format('Y-m-d');
    @endphp

    <section>
        @if($session_locked)
        <p class="planning-entry-locked">
            {{ __('messages.session_locked_by_invoice') }}
        </p>
        @endif

        <form action="{{route('planning.update', $planning->id)}}" method="post" class="group-form nice-form planning-session-form">
            @csrf
            @method('put')

            <fieldset
                class="planning-session-form__fields"
                {{ $session_locked ? 'disabled' : '' }}
                x-data="{
                    durationLabel: @js($initialDurationLabel),
                    durationInvalid: false,
                    hourlyRateLabel: @js($initialHourlyRateLabel),
                    billedAmountLabel: @js($initialBilledAmountLabel),
                    hourUnit: @js(__('messages.hours_initial')),
                    courseRates: @js($courseRates),
                    formatDuration(minutes) {
                        if (minutes <= 0) {
                            return '—';
                        }

                        const hours = Math.floor(minutes / 60);
                        const mins = minutes % 60;

                        if (hours > 0 && mins > 0) {
                            return `${hours} ${this.hourUnit} ${mins}`;
                        }

                        if (hours > 0) {
                            return `${hours} ${this.hourUnit}`;
                        }

                        return `${mins} min`;
                    },
                    formatMoney(amount) {
                        return new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(amount) + ' €';
                    },
                    formatRate(rate) {
                        return new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(rate) + ' €/h';
                    },
                    billableMultiplier(billableRate, courseRate) {
                        let multiplier = billableRate <= 0 ? 1.0 : billableRate;

                        if (multiplier > 1 && Math.abs(multiplier - courseRate) < 0.001) {
                            return 1.0;
                        }

                        if (multiplier > 1) {
                            return multiplier / 100;
                        }

                        return multiplier;
                    },
                    courseRate() {
                        const id = this.$refs.courseId?.value;
                        const rate = Number(this.courseRates[id] ?? 0);

                        return Number.isFinite(rate) ? rate : 0;
                    },
                    durationMinutes() {
                        const beginTotal = (parseInt(this.$refs.beginHour.value, 10) * 60) + parseInt(this.$refs.beginMinutes.value, 10);
                        const endTotal = (parseInt(this.$refs.endHour.value, 10) * 60) + parseInt(this.$refs.endMinutes.value, 10);

                        return endTotal - beginTotal;
                    },
                    updateComputed() {
                        const minutes = this.durationMinutes();
                        const rate = this.courseRate();
                        const billableRate = parseFloat(String(this.$refs.billableRate.value).replace(',', '.')) || 0;

                        this.durationInvalid = minutes <= 0;
                        this.durationLabel = this.formatDuration(minutes);
                        this.hourlyRateLabel = this.formatRate(rate);

                        if (minutes <= 0) {
                            this.billedAmountLabel = '—';
                            return;
                        }

                        const amount = (minutes / 60) * rate * this.billableMultiplier(billableRate, rate);
                        this.billedAmountLabel = this.formatMoney(amount);
                    },
                }"
                x-init="updateComputed()"
            >
            <div class="planning-session-form__row planning-session-form__row--when">
                <div class="form-group planning-session-form__field">
                    <label for="day" class="form-label">{{ __('messages.date') }}</label>
                    <div class="planning-date-fields">
                        <select id="day" name="day" class="form-input planning-date-fields__day">
                            @for($d=1;$d<32;$d++)
                                <option value="{{$d}}" @if((int) $d === (int) $begin_day) selected @endif>{{$d}}</option>
                            @endfor
                        </select>
                        <select id="month" name="month" class="form-input planning-date-fields__month" aria-label="{{ __('messages.date') }}">
                            @foreach ($months as $index => $monthName)
                                <option value="{{ $index + 1 }}" @selected((int) $index + 1 === (int) $begin_month)>{{ $monthName }}</option>
                            @endforeach
                        </select>
                        <select id="year" name="year" class="form-input planning-date-fields__year" aria-label="{{ __('messages.year') }}">
                            @foreach ($years as $year)
                                <option value="{{ $year }}" @selected((int) $year === (int) $begin_year)>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group planning-session-form__field planning-session-form__field--time">
                    <label for="begin" class="form-label">{{ __('messages.begin') }}</label>
                    <div class="planning-time-fields">
                        <select name="hour" id="begin" class="form-input" x-ref="beginHour" x-on:change="updateComputed()">
                            @for($h=8;$h<22;$h++)
                                <option value="{{$h}}" @if((int) $h === (int) $begin_hour) selected @endif>{{$h}}</option>
                            @endfor
                        </select>
                        <select name="minutes" class="form-input" x-ref="beginMinutes" x-on:change="updateComputed()">
                            @for($m=0;$m<60;$m+=5)
                                <option value="{{$m}}" @if((int) $m === (int) $begin_minutes) selected @endif>{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}</option>
                            @endfor
                        </select>
                    </div>
                </div>

                <div class="form-group planning-session-form__field planning-session-form__field--time">
                    <label for="end" class="form-label">{{ __('messages.end') }}</label>
                    <div class="planning-time-fields">
                        <select name="end_hour" id="end" class="form-input" x-ref="endHour" x-on:change="updateComputed()">
                            @for($h=8;$h<22;$h++)
                                <option value="{{$h}}" @if((int) $h === (int) $end_hour) selected @endif>{{$h}}</option>
                            @endfor
                        </select>
                        <select name="end_minutes" class="form-input" x-ref="endMinutes" x-on:change="updateComputed()">
                            @for($m=0;$m<60;$m+=5)
                                <option value="{{$m}}" @if((int) $m === (int) $end_minutes) selected @endif>{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
            </div>

            <div class="planning-session-form__row planning-session-form__row--billing">
                <div class="form-group planning-session-form__field" x-bind:class="{ 'planning-duration--invalid': durationInvalid }">
                    <span class="form-label">{{ __('messages.duration_indicative') }}</span>
                    <div class="planning-session-form__readonly">
                        <span class="planning-duration__value" x-text="durationLabel">{{ $initialDurationLabel }}</span>
                    </div>
                </div>

                <div class="form-group planning-session-form__field">
                    <span class="form-label">{{ __('messages.hourly_rate') }}</span>
                    <div class="planning-session-form__readonly">
                        <span class="planning-session-form__value" x-text="hourlyRateLabel">{{ $initialHourlyRateLabel }}</span>
                    </div>
                </div>

                <div class="form-group planning-session-form__field planning-session-form__rate">
                    <label for="rate" class="form-label">{{ __('messages.billable_rate') }}</label>
                    <x-text-input type="text" id="rate" class="planning-session-form__rate-input" value="{{$planning->billable_rate}}" name="billable_rate" x-ref="billableRate" x-on:input="updateComputed()" />
                </div>

                <div class="form-group planning-session-form__field">
                    <span class="form-label">{{ __('messages.billed_amount') }}</span>
                    <div class="planning-session-form__readonly">
                        <span class="planning-session-form__value" x-text="billedAmountLabel">{{ $initialBilledAmountLabel }}</span>
                    </div>
                </div>
            </div>

            <div class="planning-session-form__row planning-session-form__row--assignments">
                <div class="form-group planning-session-form__field">
                    <label for="group_id" class="form-label">{{ __('messages.group') }}</label>
                    <select id="group_id" name="group_id" class="form-input">
                        @foreach ($groups as $group)
                        <option value="{{$group->id}}" @if($group->id == $planning->group_id) selected @endif>
                            {{$group->id}} {{$group->name}}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group planning-session-form__field">
                    <label for="course_id" class="form-label">{{ __('messages.course') }}</label>
                    <select id="course_id" name="course_id" class="form-input" x-ref="courseId" x-on:change="updateComputed()">
                        @foreach ($courses as $course)
                        <option value="{{$course->id}}" @if($course->id == $planning->course_id) selected @endif>
                            {{$course->name}}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            </fieldset>

            <div class="form-actions">
                <a class="btn btn-secondary" href="{{ route('planning.index') }}">{{ __('messages.cancel') }}</a>
                <x-button-primary :disabled="$session_locked">{{ __('messages.plan') }}</x-button-primary>
            </div>
        </form>

        @if (! $session_locked)
            <x-planning-duplicate-actions
                variant="inline"
                :planning-id="$planning->id"
                :event-label="$duplicateLabel"
                :default-date="$duplicateDefaultDate"
            />
        @endif
    </section>

    <x-planning-duplicate-dialog />
</x-app-layout>
