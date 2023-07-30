<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Program Modification') }}
        </h2>
    </x-slot>

    <x-nice-box color="white">
        {{$program->name}}
    </x-nice-box>
</x-app-layout>