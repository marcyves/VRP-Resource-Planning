@php
    $monthPadded = str_pad((string) $month, 2, '0', STR_PAD_LEFT);
    $dayPadded = str_pad((string) $day, 2, '0', STR_PAD_LEFT);
    $isoDate = "{$year}-{$monthPadded}-{$dayPadded}";
@endphp

<div class="calCell calDay">
    @if (Auth::user()->getMode() == 'Edit' && session('course_id'))
        <button
            type="submit"
            form="planning-create-form"
            name="date"
            value="{{ $isoDate }}"
            class="planning-date planning-date--create"
            aria-label="{{ __('messages.planning_create_on_day', ['date' => $isoDate]) }}"
        >
            {{ $day }}
        </button>
    @elseif (Auth::user()->getMode() == 'Edit')
        <div
            class="planning-date planning-date--disabled"
            title="{{ __('messages.planning_select_course_first') }}"
        >
            {{ $day }}
        </div>
    @else
        <div class="planning-date">
            {{ $day }}
        </div>
    @endif
    @php
    $day_gain = 0;
    $day_hours = 0;
    @endphp
    @foreach ($planning as $event)
    @php
    $begin_date = explode(" ", $event->begin)[0];
    $begin_day = explode("-", $begin_date)[2];
    if ((int)$begin_day == $day){
    $day_gain += $event->session_length * $event->rate;
    $day_hours += $event->session_length;
    $eventLabel = \Carbon\Carbon::parse($event->begin)->format('H:i') . ': ' . $event->short_name . ' (' . $event->group_short_name . ')';
    $eventDate = \Carbon\Carbon::parse($event->begin)->format('d/m/Y');
    $canEdit = Auth::user()->getMode() == 'Edit' && ! $event->invoice_id;
    @endphp
    <div class="planning-entry{{ $canEdit ? ' planning-entry--editable' : '' }}">
        @if ($canEdit)
            <a
                href="{{ route('planning.edit', $event->id) }}"
                class="planning-text planning-text--link"
                aria-label="{{ __('messages.edit') }} — {{ $eventLabel }}"
            >
        @else
            <div class="planning-text">
        @endif
            <div class="planning-event-info">
                {{ \Carbon\Carbon::parse($event->begin)->format('H:i') }}: {{ $event->short_name }} ({{ $event->group_short_name }})
            </div>
            <div class="planning-event-gain">
                {{ \Carbon\Carbon::parse($event->end)->format('H:i') }}: {{ number_format($event->session_length * $event->rate, 2) }} €
            </div>
            @if($event->invoice_id)
            <div class="planning-entry-locked" title="{{ __('messages.session_locked_by_invoice') }}">
                {{ $event->invoice_id }}
            </div>
            @endif
        @if ($canEdit)
            </a>
        @else
            </div>
        @endif
        @if ($canEdit)
        <div class="planning-tools">
            <button
                type="button"
                class="icon icon--delete"
                aria-label="{{ __('messages.delete') }}"
                x-data=""
                x-on:click.prevent="$store.planningDelete.request(@js(route('planning.delete', $event->id)), @js($eventLabel), @js($eventDate))"
            >
                <img src="{{ asset('icons/trash.svg') }}" alt="" width="18" height="18" decoding="async">
            </button>
        </div>
        @endif
    </div>
    @php
    }
    @endphp
    @endforeach
    <div class="spacer"></div>
    <div class="planning-total">
        {{$day_hours}} {{ __('messages.hours_initial') }} - {{number_format($day_gain,2)}} €
    </div>
</div>