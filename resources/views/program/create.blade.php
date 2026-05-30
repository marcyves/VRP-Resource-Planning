<x-app-layout>
    <x-slot name="header">
        <h2>{{ __('messages.program_create') }}</h2>
    </x-slot>

    <x-workload-module-tabs />

    <section>
        <form action="{{ route('program.store') }}" method="post" class="group-form nice-form">
            @csrf

            <div class="form-group">
                <x-input-label for="name">{{ __('messages.name') }}</x-input-label>
                <x-text-input type="text" name="name" id="name" value="{{ old('name') }}" required />
                <x-input-error :messages="$errors->get('name')" />
            </div>

            <div class="form-actions">
                <a class="btn btn-secondary" href="{{ route('program.index') }}">{{ __('messages.cancel') }}</a>
                <x-button-primary>{{ __('messages.create') }}</x-button-primary>
            </div>
        </form>
    </section>
</x-app-layout>
