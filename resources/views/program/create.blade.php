<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('New Program Creation') }}
        </h2>
    </x-slot>

    <section  class="nice-page">

<form action="{{route('program.store')}}" method="post">
    @csrf
    <x-input-label>Name</x-input-label>
    <x-text-input type="text" name="name" />
    <br class="my-4">
    <x-primary-button>Create</x-primary-button>
</form>
    </section>
</x-app-layout>