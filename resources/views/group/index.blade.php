<x-app-layout>
    <x-slot name="header">
        <h2>{{ __('messages.groups_list') }}</h2>
    </x-slot>

    <x-module-tabs :tabs="[
        ['href' => route('dashboard'), 'label' => __('messages.workload_plan'), 'active' => request()->routeIs('dashboard', 'school.dashboard')],
        ['href' => route('school.index'), 'label' => __('messages.schools'), 'active' => request()->routeIs('school.index', 'school.list', 'school.show', 'school.create', 'school.edit', 'school.add', 'course.*')],
        ['href' => route('program.index'), 'label' => __('messages.programs'), 'active' => request()->routeIs('program.*')],
        ['href' => route('group.index'), 'label' => __('messages.groups'), 'active' => request()->routeIs('group.*')],
    ]" />

    <section>
        <ul class="group-list">
            @foreach ($groups as $group)
            <li>
                <x-group-details :group="$group" :occurences="$occurences" :active="true" />
            </li>
            @endforeach
        </ul>
    </section>

    @if($inactive->count() > 0 || request('search'))
    <div class="group-section-header">
        <h3 class="group-title">{{ __('messages.inactive_groups') }}</h3>
        <form action="{{ route('group.index') }}" method="GET" class="nav-form">
            <x-text-input type="text" name="search" placeholder="{{ __('messages.search') }}" value="{{ request('search') }}" />
            <x-button-primary type="submit">{{ __('messages.search') }}</x-button-primary>
            @if(request('search'))
            <a href="{{ route('group.index') }}" class="btn btn-secondary">{{ __('messages.clear') }}</a>
            @endif
        </form>
    </div>

    <section>
        <ul class="group-grid">
            @foreach ($inactive as $group)
            <li>
                <x-group-details :group="$group" :occurences="$occurences" :active="false" />
            </li>
            @endforeach
        </ul>
        <div class="pagination-container">
            {{ $inactive->links() }}
        </div>
    </section>
    @endif

    @if(Auth::user()->getMode() == "Edit")
    <section>
        <form action="{{route('group.save', 0)}}" method="post" class="group-form">
            @csrf
            <div class="form-group">
                <x-text-input type="text" name="name" id="name" placeholder="{{ __('messages.name') }}" />
                <x-input-error :messages="$errors->get('name')" />
            </div>
            <div class="form-group">
                <x-text-input type="text" name="short_name" id="short_name" placeholder="{{ __('messages.short_name') }}" />
                <x-input-error :messages="$errors->get('short_name')" />
            </div>
            <div class="form-group">
                <x-text-input type="text" name="size" id="size" placeholder="{{ __('messages.size') }}" />
                <x-input-error :messages="$errors->get('size')" />
            </div>
            <div class="form-group">
                <x-text-input type="text" name="year" id="year" placeholder="{{ __('messages.year') }}" />
                <x-input-error :messages="$errors->get('year')" />
            </div>
            <x-button-primary>{{ __('messages.group_create') }}</x-button-primary>
        </form>
    </section>
    @endif
</x-app-layout>