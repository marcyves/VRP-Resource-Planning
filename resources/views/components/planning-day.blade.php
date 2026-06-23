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
    $duplicateDefaultDate = \Carbon\Carbon::parse($event->begin)->addDay()->format('Y-m-d');
    @endphp
    <div class="planning-entry{{ $canEdit ? ' planning-entry--editable' : '' }}">
        @if ($canEdit)
            <a
                href="{{ route('planning.edit', $event->id) }}"
                class="planning-text planning-text--link"
                aria-label="{{ __('messages.edit') }} — {{ $eventLabel }}"
            >
                <div class="planning-event-info">
                    {{ \Carbon\Carbon::parse($event->begin)->format('H:i') }}: {{ $event->short_name }} ({{ $event->group_short_name }})
                </div>
                <div class="planning-event-gain">
                    {{ \Carbon\Carbon::parse($event->end)->format('H:i') }}: {{ number_format($event->session_length * $event->rate, 2) }} €
                </div>
            </a>
            <div class="planning-tools" role="toolbar" aria-label="{{ __('messages.actions') }}">
                <div class="planning-quick-actions" role="group" aria-label="{{ __('messages.duplicate') }}">
                    <form action="{{ route('planning.duplicate', $event->id) }}" method="post">
                        @csrf
                        <input type="hidden" name="offset" value="tomorrow">
                        <button type="submit" class="planning-quick-action" title="{{ __('messages.planning_duplicate_tomorrow') }}">
                            +1j
                        </button>
                    </form>
                    <form action="{{ route('planning.duplicate', $event->id) }}" method="post">
                        @csrf
                        <input type="hidden" name="offset" value="next_week">
                        <button type="submit" class="planning-quick-action" title="{{ __('messages.planning_duplicate_next_week') }}">
                            +1s
                        </button>
                    </form>
                    <button
                        type="button"
                        class="planning-quick-action"
                        title="{{ __('messages.planning_duplicate_custom_date') }}"
                        data-planning-duplicate-open
                        data-duplicate-url="{{ route('planning.duplicate', $event->id) }}"
                        data-duplicate-date="{{ $duplicateDefaultDate }}"
                    >
                        …
                    </button>
                </div>
                <button
                    type="button"
                    class="icon icon--delete"
                    aria-label="{{ __('messages.delete') }}"
                    data-planning-delete
                    data-delete-url="{{ route('planning.delete', $event->id) }}"
                    data-delete-label="{{ e($eventLabel) }}"
                    data-delete-date="{{ e($eventDate) }}"
                >
                    <img src="{{ asset('icons/trash.svg') }}" alt="" width="18" height="18" decoding="async">
                </button>
            </div>
        @else
            <div class="planning-text">
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
