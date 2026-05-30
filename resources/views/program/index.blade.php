<x-app-layout>
    <x-slot name="header">
        <h2>
            {{ __('messages.programs') }}
        </h2>
    </x-slot>

    <x-workload-module-tabs />

    <section>
        <ul class="program-grid">
            @foreach ($programs as $program)
                <x-program-card :program="$program" />
            @endforeach
        </ul>
    </section>

    <x-modal name="confirm-program-delete" focusable maxWidth="md">
        <div class="profile-modal-form">
            <h2 class="modal-title">{{ __('messages.program_delete_confirm_title') }}</h2>
            <p class="form-hint" x-show="$store.programDelete.name">
                <strong>{{ __('messages.name') }} :</strong>
                <span x-text="$store.programDelete.name"></span>
            </p>
            <p class="form-hint">{{ __('messages.program_delete_confirm_description') }}</p>
            <div class="form-actions">
                <x-button-secondary type="button" x-on:click="$dispatch('close')">
                    {{ __('messages.cancel') }}
                </x-button-secondary>
                <form x-bind:action="$store.programDelete.url" method="post">
                    @csrf
                    @method('delete')
                    <x-button-danger type="submit">{{ __('messages.delete') }}</x-button-danger>
                </form>
            </div>
        </div>
    </x-modal>

    @if (Auth::user()->getMode() == 'Edit')
        <section>
            <form action="{{ route('program.store') }}" method="post" class="group-form nice-form">
                @csrf
                <div class="form-group">
                    <x-input-label for="program_name">{{ __('messages.name') }}</x-input-label>
                    <x-text-input type="text" name="name" id="program_name" placeholder="{{ __('messages.name') }}" required />
                </div>
                <div class="form-actions">
                    <x-button-primary>{{ __('messages.program_create') }}</x-button-primary>
                </div>
            </form>
        </section>
    @endif
</x-app-layout>
