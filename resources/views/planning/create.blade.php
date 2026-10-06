<x-app-layout>
    <x-slot name="header">
        <h2>{{ __('messages.group_planning') }}: {{ old('date', $date) }}</h2>
    </x-slot>

    <x-scheduling-module-tabs />

    @php
    $formatMoney = function (float $amount): string {
        return number_format($amount, 2, ',', ' ').' €';
    };

    $formatRate = function (float $rate): string {
        return number_format($rate, 2, ',', ' ').' €/h';
    };

    $formatSessionLength = function ($value): string {
        $hours = \App\Http\Utility\Tools::parseSessionLengthHours($value);
        if (abs($hours - round($hours)) < 0.001) {
            return (string) (int) round($hours);
        }

        return rtrim(rtrim(number_format($hours, 2, ',', ''), '0'), ',');
    };

    $sessionLengthLabel = $formatSessionLength(old('session_length', $session_length));
    $initialHourlyRateLabel = $formatRate((float) $hourlyRate);
    $initialBilledAmountLabel = $formatMoney((float) $billedAmount);
    $dateLabel = \Carbon\Carbon::parse(old('date', $date))->format('d/m/Y');
    @endphp

    <section>
        <form action="{{ route('planning.store') }}" method="post" class="group-form nice-form planning-session-form planning-session-create-form">
            @csrf
            <input type="hidden" name="date" value="{{ old('date', $date) }}">
            <input type="hidden" name="course" value="{{ old('course', $course->id) }}">

            <fieldset
                class="planning-session-form__fields"
                x-data="{
                    billedAmountLabel: @js($initialBilledAmountLabel),
                    hourlyRateLabel: @js($initialHourlyRateLabel),
                    durationInvalid: false,
                    syncing: false,
                    hourlyRate: @js((float) $hourlyRate),
                    formatMoney(amount) {
                        return new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(amount) + ' €';
                    },
                    formatRate(rate) {
                        return new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(rate) + ' €/h';
                    },
                    formatDurationHours(hours) {
                        const rounded = Math.round(hours * 100) / 100;
                        if (!Number.isFinite(rounded) || rounded <= 0) {
                            return '';
                        }
                        if (Number.isInteger(rounded)) {
                            return String(rounded);
                        }
                        return String(rounded).replace('.', ',');
                    },
                    parseDurationHours(value) {
                        const n = parseFloat(String(value).replace(',', '.'));
                        return Number.isFinite(n) ? Math.max(0, n) : 0;
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
                    durationMinutes() {
                        const beginTotal = (parseInt(this.$refs.beginHour.value, 10) * 60) + parseInt(this.$refs.beginMinutes.value, 10);
                        const endTotal = (parseInt(this.$refs.endHour.value, 10) * 60) + parseInt(this.$refs.endMinutes.value, 10);

                        return endTotal - beginTotal;
                    },
                    setSelectValue(select, value) {
                        const str = String(value);
                        if ([...select.options].some((option) => option.value === str)) {
                            select.value = str;
                        }
                    },
                    applyDurationToEnd() {
                        if (this.syncing) {
                            return;
                        }

                        this.syncing = true;
                        const beginTotal = (parseInt(this.$refs.beginHour.value, 10) * 60) + parseInt(this.$refs.beginMinutes.value, 10);
                        const durationMinutes = Math.round(this.parseDurationHours(this.$refs.sessionLength.value) * 60);
                        const endTotal = Math.round((beginTotal + durationMinutes) / 5) * 5;
                        this.setSelectValue(this.$refs.endHour, Math.floor(endTotal / 60));
                        this.setSelectValue(this.$refs.endMinutes, endTotal % 60);
                        this.updateAmount();
                        this.syncing = false;
                    },
                    applyEndToDuration() {
                        if (this.syncing) {
                            return;
                        }

                        this.syncing = true;
                        const minutes = this.durationMinutes();
                        if (minutes > 0) {
                            this.$refs.sessionLength.value = this.formatDurationHours(minutes / 60);
                        }
                        this.updateAmount();
                        this.syncing = false;
                    },
                    updateAmount() {
                        const minutes = this.durationMinutes();
                        const rate = this.hourlyRate;

                        this.durationInvalid = minutes <= 0;
                        this.hourlyRateLabel = this.formatRate(rate);

                        if (minutes <= 0) {
                            this.billedAmountLabel = '—';
                            return;
                        }

                        const amount = (minutes / 60) * rate * this.billableMultiplier(1, rate);
                        this.billedAmountLabel = this.formatMoney(amount);
                    },
                }"
                x-init="updateAmount()"
            >
            <div class="planning-session-form__row planning-session-form__row--when">
                <div class="form-group planning-session-form__field">
                    <span class="form-label">{{ __('messages.date') }}</span>
                    <div class="planning-session-form__readonly">
                        <span class="planning-session-form__value">{{ $dateLabel }}</span>
                    </div>
                </div>

                <div class="form-group planning-session-form__field planning-session-form__field--time">
                    <label for="begin-hour" class="form-label">{{ __('messages.begin') }}</label>
                    <div class="planning-time-fields">
                        <select id="begin-hour" name="hour" class="form-input" x-ref="beginHour" x-on:change="applyDurationToEnd()">
                            @for ($h = 8; $h < 22; $h++)
                                <option value="{{ $h }}" @selected((string) old('hour', (string) $hour) === (string) $h)>{{ $h }}</option>
                            @endfor
                        </select>
                        <select name="minutes" class="form-input" aria-label="{{ __('messages.begin') }}" x-ref="beginMinutes" x-on:change="applyDurationToEnd()">
                            @for ($m = 0; $m < 60; $m += 5)
                                <option value="{{ $m }}" @selected((string) old('minutes', (string) $minutes) === (string) $m)>{{ str_pad((string) $m, 2, '0', STR_PAD_LEFT) }}</option>
                            @endfor
                        </select>
                    </div>
                    <x-input-error :messages="$errors->get('hour')" />
                    <x-input-error :messages="$errors->get('minutes')" />
                </div>

                <div class="form-group planning-session-form__field planning-session-form__field--time">
                    <label for="end" class="form-label">{{ __('messages.end') }}</label>
                    <div class="planning-time-fields">
                        <select id="end" name="end_hour" class="form-input" x-ref="endHour" x-on:change="applyEndToDuration()">
                            @for ($h = 8; $h < 24; $h++)
                                <option value="{{ $h }}" @selected((string) old('end_hour', (string) $end_hour) === (string) $h)>{{ $h }}</option>
                            @endfor
                        </select>
                        <select name="end_minutes" class="form-input" aria-label="{{ __('messages.end') }}" x-ref="endMinutes" x-on:change="applyEndToDuration()">
                            @for ($m = 0; $m < 60; $m += 5)
                                <option value="{{ $m }}" @selected((string) old('end_minutes', (string) $end_minutes) === (string) $m)>{{ str_pad((string) $m, 2, '0', STR_PAD_LEFT) }}</option>
                            @endfor
                        </select>
                    </div>
                    <x-input-error :messages="$errors->get('end_hour')" />
                    <x-input-error :messages="$errors->get('end_minutes')" />
                </div>
            </div>

            <div class="planning-session-form__row planning-session-form__row--billing planning-session-form__row--billing-create">
                <div class="form-group planning-session-form__field" x-bind:class="{ 'planning-duration--invalid': durationInvalid }">
                    <label for="session_length" class="form-label">{{ __('messages.duration_standard') }}</label>
                    <div class="planning-session-form__duration">
                        <x-text-input
                            type="text"
                            inputmode="decimal"
                            id="session_length"
                            name="session_length"
                            class="planning-session-form__duration-input"
                            value="{{ $sessionLengthLabel }}"
                            x-ref="sessionLength"
                            x-on:input="applyDurationToEnd()"
                            x-on:change="applyDurationToEnd()"
                            aria-label="{{ __('messages.duration_standard') }}"
                        />
                        <span class="planning-session-form__duration-unit">{{ __('messages.hours_initial') }}</span>
                    </div>
                    <x-input-error :messages="$errors->get('session_length')" />
                </div>

                <div class="form-group planning-session-form__field">
                    <span class="form-label">{{ __('messages.hourly_rate') }}</span>
                    <div class="planning-session-form__readonly">
                        <span class="planning-session-form__value" x-text="hourlyRateLabel">{{ $initialHourlyRateLabel }}</span>
                    </div>
                </div>

                <div class="form-group planning-session-form__field">
                    <span class="form-label">{{ __('messages.intervention_amount') }}</span>
                    <div class="planning-session-form__readonly">
                        <span class="planning-session-form__value" x-text="billedAmountLabel">{{ $initialBilledAmountLabel }}</span>
                    </div>
                </div>
            </div>

            <div class="planning-session-form__row planning-session-form__row--assignments">
                <div class="form-group planning-session-form__field">
                    <label for="group" class="form-label">{{ __('messages.group') }}</label>
                    <select id="group" name="group" class="form-input">
                        <option value="0" @selected((string) old('group', '0') === '0')>{{ __('messages.new_group_below') }}</option>
                        @foreach ($groups as $group)
                        @if($group->sessions == 0 or $group->sessions == $course->sessions)
                        <option value="{{ $group->id }}" @selected((string) old('group', '0') === (string) $group->id)>
                            {{ $group->name }}
                        </option>
                        @endif
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('group')" />
                </div>

                <div class="form-group planning-session-form__field">
                    <span class="form-label">{{ __('messages.course') }}</span>
                    <div class="planning-session-form__readonly">
                        <span class="planning-session-form__value">{{ $course->name }}</span>
                    </div>
                </div>
            </div>
            </fieldset>

            <div class="planning-session-create-form__fields">
                <x-form-group-create :course_id="$course->id" :details-row="true" />
            </div>

            <div class="form-actions">
                <a class="btn btn-secondary" href="{{ route('planning.index') }}">{{ __('messages.cancel') }}</a>
                <x-button-primary>{{ __('messages.plan') }}</x-button-primary>
            </div>
        </form>
    </section>
</x-app-layout>
