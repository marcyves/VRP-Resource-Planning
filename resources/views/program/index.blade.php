<x-app-layout>
    <x-slot name="header">
        <h2>
            {{ __('messages.programs') }}
        </h2>
    </x-slot>

    <x-workload-module-tabs />

    <section>
        <x-program-table :programs="$programs" />
    </section>

    <x-confirm-delete-modal
        name="confirm-program-delete"
        store="programDelete"
        entity="program"
        :hints="[['field' => 'name', 'label' => __('messages.name')]]"
    />

    @if (Auth::user()->getMode() == 'Edit')
        <section>
            <form action="{{ route('program.store') }}" method="post" class="group-form nice-form">
                @csrf
                <div class="form-group">
                    <x-input-label for="program_name">{{ __('messages.name') }}</x-input-label>
                    <x-text-input type="text" name="name" id="program_name" placeholder="{{ __('messages.name') }}" required />
                </div>
                <div class="form-group">
                    <x-input-label for="program_short_description">{{ __('messages.short_description') }}</x-input-label>
                    <x-text-input type="text" name="short_description" id="program_short_description" placeholder="{{ __('messages.short_description') }}" maxlength="80" />
                    <p class="form-hint">{{ __('messages.program_short_description_help') }}</p>
                </div>
                <div class="form-actions">
                    <x-button-primary>{{ __('messages.program_create') }}</x-button-primary>
                </div>
            </form>
        </section>
    @endif
</x-app-layout>
