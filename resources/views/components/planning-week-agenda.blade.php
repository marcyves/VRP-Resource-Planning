@props(['days', 'planning', 'currentMonth'])

@php
    $hours = \App\Http\Utility\Tools::weekAgendaHours();
    $slotHours = array_slice($hours, 0, -1);
    $locale = \App\Support\TerminologyLocale::normalizeBaseLocale(app()->getLocale());
    $canCreate = Auth::user()->getMode() == 'Edit' && session('course_id');
@endphp

<div class="week-agenda" id="calWrap">
    <div class="week-agenda__corner" aria-hidden="true"></div>

    @foreach ($days as $date)
        @php
            $isoDate = $date->toDateString();
            $outside = $date->month !== (int) $currentMonth;
        @endphp
        <div @class([
            'week-agenda__day-head',
            'week-agenda__day-head--outside' => $outside,
            'week-agenda__day-head--today' => $date->isToday(),
        ])>
            <span class="week-agenda__weekday">{{ $date->copy()->locale($locale)->translatedFormat('D') }}</span>
            @if ($canCreate)
                <button
                    type="submit"
                    form="planning-create-form"
                    name="date"
                    value="{{ $isoDate }}"
                    class="planning-date planning-date--create"
                    aria-label="{{ __('messages.planning_create_on_day', ['date' => $isoDate]) }}"
                >
                    {{ $date->day }}
                </button>
            @elseif (Auth::user()->getMode() == 'Edit')
                <div class="planning-date planning-date--disabled" title="{{ __('messages.planning_select_course_first') }}">
                    {{ $date->day }}
                </div>
            @else
                <div class="planning-date">{{ $date->day }}</div>
            @endif
        </div>
    @endforeach

    <div class="week-agenda__hours" aria-hidden="true">
        @foreach ($slotHours as $hour)
            <div class="week-agenda__hour">{{ $hour }}h</div>
        @endforeach
        <div class="week-agenda__hour week-agenda__hour--end">{{ \App\Http\Utility\Tools::WEEK_AGENDA_END_HOUR }}h</div>
    </div>

    @foreach ($days as $date)
        @php
            $isoDate = $date->toDateString();
            $outside = $date->month !== (int) $currentMonth;
            $dayEvents = $planning->filter(function ($event) use ($isoDate) {
                return explode(' ', $event->begin)[0] === $isoDate;
            });
        @endphp
        <div @class([
            'week-agenda__column',
            'week-agenda__column--outside' => $outside,
            'week-agenda__column--today' => $date->isToday(),
        ])>
            <div class="week-agenda__slots">
                @foreach ($slotHours as $hour)
                    <div class="week-agenda__slot"></div>
                @endforeach
            </div>
            <div class="week-agenda__events">
                @foreach ($dayEvents as $event)
                    @php
                        $slot = \App\Http\Utility\Tools::weekEventPosition(
                            \Carbon\Carbon::parse($event->begin),
                            \Carbon\Carbon::parse($event->end)
                        );
                    @endphp
                    @if ($slot['visible'])
                        <x-planning-week-event :event="$event" :top="$slot['top']" :height="$slot['height']" />
                    @endif
                @endforeach
            </div>
        </div>
    @endforeach
</div>
