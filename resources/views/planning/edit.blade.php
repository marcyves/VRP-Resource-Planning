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

    $initialDurationLabel = $formatDuration(
        \Carbon\Carbon::parse($planning->begin)->diffInMinutes(\Carbon\Carbon::parse($planning->end))
    );

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

            <fieldset {{ $session_locked ? 'disabled' : '' }}>
            <div class="form-group">
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

            <div class="form-group planning-time-group">
                <div
                    class="planning-time-row"
                    x-data="{
                        durationLabel: @js($initialDurationLabel),
                        durationInvalid: false,
                        hourUnit: @js(__('messages.hours_initial')),
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
                        updateDuration() {
                            const beginTotal = (parseInt(this.$refs.beginHour.value, 10) * 60) + parseInt(this.$refs.beginMinutes.value, 10);
                            const endTotal = (parseInt(this.$refs.endHour.value, 10) * 60) + parseInt(this.$refs.endMinutes.value, 10);
                            const minutes = endTotal - beginTotal;

                            this.durationInvalid = minutes <= 0;
                            this.durationLabel = this.formatDuration(minutes);
                        },
                    }"
                    x-init="updateDuration()"
                >
                    <div class="planning-time-slot">
                        <label for="begin" class="planning-time-slot__label">{{ __('messages.begin') }}</label>
                        <div class="planning-time-fields">
                            <select name="hour" id="begin" class="form-input" x-ref="beginHour" x-on:change="updateDuration()">
                                @for($h=8;$h<22;$h++)
                                    <option value="{{$h}}" @if((int) $h === (int) $begin_hour) selected @endif>{{$h}}</option>
                                @endfor
                            </select>
                            <select name="minutes" class="form-input" x-ref="beginMinutes" x-on:change="updateDuration()">
                                @for($m=0;$m<60;$m+=5)
                                    <option value="{{$m}}" @if((int) $m === (int) $begin_minutes) selected @endif>{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <div class="planning-time-slot">
                        <label for="end" class="planning-time-slot__label">{{ __('messages.end') }}</label>
                        <div class="planning-time-fields">
                            <select name="end_hour" id="end" class="form-input" x-ref="endHour" x-on:change="updateDuration()">
                                @for($h=8;$h<22;$h++)
                                    <option value="{{$h}}" @if((int) $h === (int) $end_hour) selected @endif>{{$h}}</option>
                                @endfor
                            </select>
                            <select name="end_minutes" class="form-input" x-ref="endMinutes" x-on:change="updateDuration()">
                                @for($m=0;$m<60;$m+=5)
                                    <option value="{{$m}}" @if((int) $m === (int) $end_minutes) selected @endif>{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <div class="planning-time-slot planning-time-slot--duration" x-bind:class="{ 'planning-duration--invalid': durationInvalid }">
                        <span class="planning-time-slot__label">{{ __('messages.duration_indicative') }}</span>
                        <div class="planning-duration__value-wrap">
                            <span class="planning-duration__value" x-text="durationLabel"></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group planning-session-form__rate">
                <label for="rate" class="form-label">{{ __('messages.billable_rate') }}</label>
                <x-text-input type="text" id="rate" class="planning-session-form__rate-input" value="{{$planning->billable_rate}}" name="billable_rate" />
            </div>

            <div class="planning-session-form__assignments">
                <div class="form-group planning-session-form__assignment">
                    <label for="group_id" class="form-label">{{ __('messages.group') }}</label>
                    <select id="group_id" name="group_id" class="form-input">
                        @foreach ($groups as $group)
                        <option value="{{$group->id}}" @if($group->id == $planning->group_id) selected @endif>
                            {{$group->id}} {{$group->name}}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group planning-session-form__assignment">
                    <label for="course_id" class="form-label">{{ __('messages.course') }}</label>
                    <select id="course_id" name="course_id" class="form-input">
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

    <x-planning-duplicate-modal />
</x-app-layout>