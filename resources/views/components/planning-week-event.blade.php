@props(['event', 'top', 'height'])

@php
    $begin = \Carbon\Carbon::parse($event->begin);
    $end = \Carbon\Carbon::parse($event->end);
    $eventLabel = $begin->format('H:i') . ': ' . $event->short_name . ' (' . $event->group_short_name . ')';
    $eventDate = $begin->format('d/m/Y');
    $canEdit = Auth::user()->getMode() == 'Edit' && ! $event->invoice_id;
    $duplicateDefaultDate = $begin->copy()->addDay()->format('Y-m-d');
@endphp

<article
    class="week-agenda__event{{ $canEdit ? ' week-agenda__event--editable' : '' }}{{ $event->invoice_id ? ' week-agenda__event--locked' : '' }}"
    style="top: {{ $top }}%; height: {{ $height }}%;"
>
    @if ($canEdit)
        <a
            href="{{ route('planning.edit', $event->id) }}"
            class="week-agenda__event-link"
            aria-label="{{ __('messages.edit') }} — {{ $eventLabel }}"
        >
            <span class="week-agenda__event-time">{{ $begin->format('H:i') }}–{{ $end->format('H:i') }}</span>
            <span class="week-agenda__event-title">{{ $event->short_name }}</span>
            <span class="week-agenda__event-group">({{ $event->group_short_name }})</span>
        </a>
        <div class="week-agenda__event-tools" role="toolbar" aria-label="{{ __('messages.actions') }}">
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
            <button
                type="button"
                class="icon icon--delete"
                aria-label="{{ __('messages.delete') }}"
                data-planning-delete
                data-delete-url="{{ route('planning.delete', $event->id) }}"
                data-delete-label="{{ e($eventLabel) }}"
                data-delete-date="{{ e($eventDate) }}"
            >
                <img src="{{ asset('icons/trash.svg') }}" alt="" width="16" height="16" decoding="async">
            </button>
        </div>
    @else
        <div class="week-agenda__event-body">
            <span class="week-agenda__event-time">{{ $begin->format('H:i') }}–{{ $end->format('H:i') }}</span>
            <span class="week-agenda__event-title">{{ $event->short_name }}</span>
            <span class="week-agenda__event-group">({{ $event->group_short_name }})</span>
            @if ($event->invoice_id)
                <span class="planning-entry-locked" title="{{ __('messages.session_locked_by_invoice') }}">{{ $event->invoice_id }}</span>
            @endif
        </div>
    @endif
</article>
