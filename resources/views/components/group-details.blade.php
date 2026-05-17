@php
    $groupOccurences = $occurences->where('group_id', $group->id);
@endphp

<div class="group-card-content" x-data="{ showSessions: true }">
    <div class="group-title">
        <a href="{{route('group.show', $group->id)}}">
            {{$group->name}} ({{$group->short_name}})
        </a>
    </div>
    <div class="group-info">
        {{$group->size}} {{ __('messages.students') }} - {{$group->year}}
    </div>

    @if($groupOccurences->isNotEmpty())
    <button type="button" class="group-sessions-toggle" x-on:click="showSessions = !showSessions" :aria-label="showSessions ? @js(__('messages.hide_sessions')) : @js(__('messages.show_sessions'))">
        <svg x-show="showSessions" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            <circle cx="12" cy="12" r="2.75" fill="none" stroke="currentColor" stroke-width="1.8"/>
        </svg>
        <svg x-show="!showSessions" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true" style="display: none;">
            <path d="M3 3l18 18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            <path d="M9.25 5.45A9.52 9.52 0 0 1 12 5c6 0 9.5 7 9.5 7a16.88 16.88 0 0 1-2.84 3.69" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M14.12 14.12A3 3 0 0 1 9.88 9.88" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            <path d="M6.53 6.53C3.89 8.38 2.5 12 2.5 12s3.5 7 9.5 7a9.7 9.7 0 0 0 4.04-.88" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </button>
    @endif

    @if(Auth::user()->getMode() == "Edit")
    <div class="group-actions">
        <form action="{{route('group.switch', $group->id)}}" method="get">
            <button class="icon icon--archive group-archive-toggle" type="submit" title="{{ $active ? __('messages.deactivate') : __('messages.activate') }}" aria-label="{{ $active ? __('messages.deactivate') : __('messages.activate') }}">
                @if($active)
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M4 7.5h16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    <path d="M6 7.5V19a1.5 1.5 0 0 0 1.5 1.5h9A1.5 1.5 0 0 0 18 19V7.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                    <path d="M8 4h8l1.5 3.5h-11L8 4Z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                    <path d="M12 10.5v5.5m0 0 2.25-2.25M12 16l-2.25-2.25" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                @else
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M4 7.5h16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    <path d="M6 7.5V19a1.5 1.5 0 0 0 1.5 1.5h9A1.5 1.5 0 0 0 18 19V7.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                    <path d="M8 4h8l1.5 3.5h-11L8 4Z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                    <path d="M12 16V10.5m0 0 2.25 2.25M12 10.5l-2.25 2.25" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                @endif
            </button>
        </form>
        <form action="{{route('group.edit', $group->id)}}" method="get">
            <x-button-edit />
        </form>
        <form action="{{route('group.destroy', $group->id)}}" method="post">
            @csrf
            @method('delete')
            <x-button-delete />
        </form>
    </div>
    @endif

@if($groupOccurences->isNotEmpty())
<ul class="flex-list group-sessions-list" x-show="showSessions" x-transition>
    @foreach($groupOccurences as $occurence)
    <li>
        <a class="group-session-link" href="{{ route('planning.edit', $occurence->planning_id) }}">
            <strong class="group-session-course">{{$occurence->course_name}}</strong>
            <span class="group-session-begin">{{ \Carbon\Carbon::parse($occurence->begin)->format('d/m/Y H:i') }}</span>
            <span class="group-session-end">{{ \Carbon\Carbon::parse($occurence->end)->format('H:i') }}</span>
        </a>
    </li>
    @endforeach
</ul>
@endif
</div>