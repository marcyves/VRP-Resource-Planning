<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('messages.school_create') }}
        </h2>
    </x-slot>

    <x-nice-box color="white">
        <form action="{{route('school.store')}}" method="post">
            @csrf
            <x-input-label>{{ __('messages.name') }}</x-input-label>
            <x-text-input type="text" name="name" id="name"/>
            <br class="my-4">
            <x-primary-button>{{ __('messages.school_create') }}</x-primary-button>
        </form>
    </x-nice-box>
</x-app-layout>