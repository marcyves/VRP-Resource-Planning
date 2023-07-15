<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <x-nice-box color="white">

<form action="{{route('school.update', $school->id)}}" method="post">
    @csrf
    @method('put')
    <x-input-label>Name</x-input-label>
    <x-text-input type="text" name="name" value="{{old('name',$school->name)}}"/>
    <br class="my-4">
    <x-primary-button>Edit</x-primary-button>
</form>
    </x-nice-box>
</x-app-layout>