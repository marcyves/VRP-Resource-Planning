<x-app-layout>
    <x-slot name="header">
        <h2>{{ __('messages.groups_list') }}</h2>
    </x-slot>

    <x-workload-module-tabs />

    <section>
        <x-group-table :groups="$groups" :occurences="$occurences" :active="true" />
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
        <x-group-table :groups="$inactive" :occurences="$occurences" :active="false" />
        <div class="pagination-container">
            {{ $inactive->links() }}
        </div>
    </section>
    @endif

    @if(Auth::user()->getMode() == "Edit")
    <section>
        <form action="{{route('group.save', 0)}}" method="post" class="group-form nice-form">
            @csrf
            <div class="form-group">
                <x-input-label for="name">{{ __('messages.name') }}</x-input-label>
                <x-text-input type="text" name="name" id="name" />
                <x-input-error :messages="$errors->get('name')" />
            </div>
            <div class="form-group">
                <x-input-label for="short_name">{{ __('messages.short_name') }}</x-input-label>
                <x-text-input type="text" name="short_name" id="short_name" />
                <x-input-error :messages="$errors->get('short_name')" />
            </div>
            <div class="form-group">
                <x-input-label for="size">{{ __('messages.size') }}</x-input-label>
                <x-text-input type="text" name="size" id="size" />
                <x-input-error :messages="$errors->get('size')" />
            </div>
            <div class="form-group">
                <x-input-label for="year">{{ __('messages.year') }}</x-input-label>
                <x-text-input type="text" name="year" id="year" />
                <x-input-error :messages="$errors->get('year')" />
            </div>
            <div class="form-actions">
                <x-button-primary>{{ __('messages.group_create') }}</x-button-primary>
            </div>
        </form>
    </section>
    @endif
</x-app-layout>
