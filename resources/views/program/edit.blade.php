<x-app-layout>
    <x-slot name="header">
        <h2>{{ __('messages.program_modification') }}</h2>
    </x-slot>

    <x-workload-module-tabs />

    <section>
        <form action="{{ route('program.update', $program->id) }}" method="post" class="group-form nice-form">
            @csrf
            @method('put')

            <div class="form-group">
                <x-input-label for="name">{{ __('messages.name') }}</x-input-label>
                <x-text-input type="text" name="name" id="name" value="{{ old('name', $program->name) }}" required />
                <x-input-error :messages="$errors->get('name')" />
            </div>

            <div class="form-actions">
                <a class="btn btn-secondary" href="{{ route('program.show', $program->id) }}">{{ __('messages.cancel') }}</a>
                <x-button-primary>{{ __('messages.update') }}</x-button-primary>
            </div>
        </form>
    </section>
</x-app-layout>
