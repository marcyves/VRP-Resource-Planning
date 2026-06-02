@props(['group', 'groupOccurences', 'active' => true])
<li>
    <div class="group-header card-content card-content--group">
        <div class="group-header__titles">
            <a
                href="{{ route('group.show', $group->id) }}"
                class="group-header__name"
                title="{{ $group->name }}"
            >{{ $group->name }}</a>
            @if ($group->short_name)
                <span class="group-header__short" title="{{ $group->short_name }}">{{ $group->short_name }}</span>
            @endif
        </div>
        @if (Auth::user()->getMode() == 'Edit')
            <div class="group-header__tools" role="toolbar" aria-label="{{ __('messages.actions') }}">
                <form action="{{ route('group.switch', $group->id) }}" method="get">
                    <button class="icon icon--archive group-archive-toggle" type="submit" title="{{ $active ? __('messages.deactivate') : __('messages.activate') }}" aria-label="{{ $active ? __('messages.deactivate') : __('messages.activate') }}">
                        @if ($active)
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
                <form action="{{ route('group.edit', $group->id) }}" method="get">
                    <x-button-edit />
                </form>
                <button
                    type="button"
                    class="icon icon--delete"
                    aria-label="{{ __('messages.delete') }}"
                    x-data=""
                    x-on:click.prevent="$store.groupDelete.request(@js(route('group.destroy', $group->id)), @js($group->name))"
                >
                    <img src="{{ asset('icons/trash.svg') }}" alt="" width="18" height="18" decoding="async">
                </button>
            </div>
        @endif
    </div>
    <dl class="group-card-stats">
        <div class="group-card-stat">
            <dt class="group-card-stat__label">{{ __('messages.size') }}</dt>
            <dd class="group-card-stat__value">{{ $group->size }} {{ __('messages.students') }}</dd>
        </div>
        <div class="group-card-stat">
            <dt class="group-card-stat__label">{{ __('messages.year') }}</dt>
            <dd class="group-card-stat__value">{{ $group->year }}</dd>
        </div>
        <div class="group-card-stat">
            <dt class="group-card-stat__label">{{ __('messages.sessions') }}</dt>
            <dd class="group-card-stat__value">
                <x-group-table-sessions :group-occurences="$groupOccurences" />
            </dd>
        </div>
    </dl>
</li>
