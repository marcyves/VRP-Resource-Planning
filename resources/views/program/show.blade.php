<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Program Details') }}
        </h2>
    </x-slot>

    <section  class="nice-box">
        {{$program->name}}
    </section>
</x-app-layout>