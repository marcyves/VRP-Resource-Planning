<x-app-layout>
    <x-slot name="header">
        <h2>
            {{ __('New Program Creation') }}
        </h2>
    </x-slot>

    <section>

        <form action="{{route('program.store')}}" method="post">
            @csrf
            <x-input-label>Name</x-input-label>
            <x-text-input type="text" name="name" />
            <br class="my-4">
            <x-button-primary>Create</x-button-primary>
        </form>
    </section>
</x-app-layout>