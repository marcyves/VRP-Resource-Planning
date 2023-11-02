<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    @foreach ($groups as $group)
    <x-nice-box color="white">
    <x-group-details :group=$group :occurences=$occurences />
    </x-nice-box>
    @endforeach
</x-app-layout>