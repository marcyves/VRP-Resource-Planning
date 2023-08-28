<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Group Planification') }}
        </h2>
    </x-slot>

    <x-nice-box color="white">

<form action="{{route('planning.update', $planning->group_id)}}" method="post">
    @csrf
    @method('put')
    <x-input-label>Begin</x-input-label>
    <x-text-input type="text" name="name" />
    <x-input-label>End</x-input-label>
    <x-text-input type="text" name="sessions" />
    <br class="my-4">
    <x-primary-button>Plan</x-primary-button>

</form>
    </x-nice-box>
</x-app-layout>