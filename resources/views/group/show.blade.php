<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Group Details') }}
        </h2>
    </x-slot>

    <x-nice-box color="white">
    </x-group-details :group=$group :occurences=[] />
    </x-nice-box>
</x-app-layout>