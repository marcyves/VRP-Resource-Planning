<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    @foreach ($groups as $group)
    <section  class="nice-box">
    <x-group-details :group=$group :occurences=$occurences />
    </section>
    @endforeach
</x-app-layout>