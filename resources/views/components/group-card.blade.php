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
                        <x-rune-icon :name="$active ? 'archive' : 'archive-restore'" :size="18" />
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
                    <x-rune-icon name="trash" :size="18" />
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
