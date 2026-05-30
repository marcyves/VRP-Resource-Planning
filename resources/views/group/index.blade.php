<x-app-layout>
    <x-slot name="header">
        <h2>{{ __('messages.groups_list') }}</h2>
    </x-slot>

    <x-workload-module-tabs />

    <section>
        <x-group-table :groups="$groups" :occurences="$occurences" :active="true" />
    </section>

    <x-modal name="confirm-group-delete" focusable maxWidth="md">
        <div class="profile-modal-form">
            <h2 class="modal-title">{{ __('messages.group_delete_confirm_title') }}</h2>
            <p class="form-hint" x-show="$store.groupDelete.name">
                <strong>{{ __('messages.name') }} :</strong>
                <span x-text="$store.groupDelete.name"></span>
            </p>
            <p class="form-hint">{{ __('messages.group_delete_confirm_description') }}</p>
            <div class="form-actions">
                <x-button-secondary type="button" x-on:click="$dispatch('close')">
                    {{ __('messages.cancel') }}
                </x-button-secondary>
                <form x-bind:action="$store.groupDelete.url" method="post">
                    @csrf
                    @method('delete')
                    <x-button-danger type="submit">{{ __('messages.delete') }}</x-button-danger>
                </form>
            </div>
        </div>
    </x-modal>

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
            <x-form-group-create :details-row="true" />
            <div class="form-actions">
                <x-button-primary>{{ __('messages.group_create') }}</x-button-primary>
            </div>
        </form>
    </section>
    @endif
</x-app-layout>
