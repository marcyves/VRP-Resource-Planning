<x-app-layout>
    <x-slot name="header">
        <h2>
            {{ __('messages.program_create') }}
        </h2>
    </x-slot>

    <section>

        <form action="{{route('program.store')}}" method="post">
            @csrf
            <x-input-label>{{ __('messages.name') }}</x-input-label>
            <x-text-input type="text" name="name" />
            <br class="my-4">
            <x-button-primary>{{ __('messages.create') }}</x-button-primary>
        </form>
    </section>
</x-app-layout>