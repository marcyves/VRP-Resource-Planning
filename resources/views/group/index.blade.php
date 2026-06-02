<x-app-layout>
    <x-slot name="header">
        <h2>{{ __('messages.groups_list') }}</h2>
    </x-slot>

    <x-workload-module-tabs />

    <p class="form-hint">{{ __('messages.groups_index_help') }}</p>
    @if($sessionCourseId && $sessionCourseName)
        <p class="form-hint form-hint--emphasis">{{ __('messages.group_will_link_session_course', ['name' => $sessionCourseName]) }}</p>
    @endif

    <section>
        <x-group-table :groups="$groups" :occurences="$occurences" :active="true" />
    </section>

    <x-confirm-delete-modal
        name="confirm-group-delete"
        store="groupDelete"
        entity="group"
        :hints="[['field' => 'name', 'label' => __('messages.name')]]"
    />

    @if($inactive->count() > 0 || request('search'))
    <section
        id="groups-inactive"
        class="school-panel groups-inactive-section"
        x-data="{ open: @js((bool) request('search')) }"
        x-init="
            if (@js((bool) request('search'))) {
                $nextTick(() => {
                    $el.scrollIntoView({ behavior: 'instant', block: 'start' });
                });
            }
        "
        aria-labelledby="groups-inactive-heading"
    >
        <div class="school-panel__box">
            <header class="school-panel__header">
                <h3 id="groups-inactive-heading" class="school-panel__title">{{ __('messages.inactive_groups') }}</h3>
                <div class="group-panel__header-actions">
                    <form action="{{ route('group.index') }}#groups-inactive" method="GET" class="nav-form group-panel__search">
                        <x-text-input type="text" name="search" placeholder="{{ __('messages.search') }}" value="{{ request('search') }}" />
                        <x-button-primary type="submit">{{ __('messages.search') }}</x-button-primary>
                        @if(request('search'))
                            <a href="{{ route('group.index') }}" class="btn btn-secondary">{{ __('messages.clear') }}</a>
                        @endif
                    </form>
                    <x-panel-toggle controls="groups-inactive-panel" />
                </div>
            </header>

            <div id="groups-inactive-panel" x-show="open" x-transition>
                <x-group-table :groups="$inactive" :occurences="$occurences" :active="false" />
                <div class="pagination-container">
                    {{ $inactive->links() }}
                </div>
            </div>
        </div>
    </section>
    @endif

    @if(Auth::user()->getMode() == "Edit")
    <section class="school-panel group-create-panel" x-data="{ open: true }">
        <div class="school-panel__box">
            <header class="school-panel__header">
                <h3 class="school-panel__title">{{ __('messages.group_create') }}</h3>
                <x-panel-toggle controls="group-create-panel" />
            </header>

            <div id="group-create-panel" x-show="open" x-transition>
                <form action="{{ route('group.save', 0) }}" method="post" class="group-form nice-form nice-form--embedded">
                    @csrf
                    <x-form-group-create :details-row="true" />
                    <div class="form-actions">
                        <x-button-primary>{{ __('messages.group_create') }}</x-button-primary>
                    </div>
                </form>
            </div>
        </div>
    </section>
    @endif
</x-app-layout>
