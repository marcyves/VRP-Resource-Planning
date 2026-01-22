<x-app-layout>
    @push('styles')
    @vite(['resources/css/groups.css'])
    @endpush

    <x-slot name="header">
        <h2>{{ __('messages.groups_list') }}</h2>
    </x-slot>

    <section class="glass-background">
        <ul class="group-list">
            @foreach ($groups as $group)
            <li class="group-card glass-background">
                <x-group-details :group="$group" :occurences="$occurences" :active="true" />
            </li>
            @endforeach
        </ul>
    </section>

    @if($inactive->count() > 0 || request('search'))
    <div class="group-section-header">
        <h3 class="group-title">{{ __('messages.inactive_groups') }}</h3>
        <form action="{{ route('group.index') }}" method="GET" class="nav-form">
            <x-text-input type="text" name="search" placeholder="Search..." value="{{ request('search') }}" />
            <x-button-primary type="submit">Search</x-button-primary>
            @if(request('search'))
            <a href="{{ route('group.index') }}" class="btn btn-secondary">Clear</a>
            @endif
        </form>
    </div>

    <section class="glass-background opacity-75">
        <ul class="group-grid">
            @foreach ($inactive as $group)
            <li class="group-card glass-background">
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
    <section class="glass-background">
        <form action="{{route('group.save', 0)}}" method="post" class="group-form glass-background-solid">
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