<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <section  class="nice-box">

<form action="{{route('group.save', $course_id)}}" method="post">
    @csrf
    <x-input-label>Name</x-input-label>
    <x-text-input type="text" name="name" />
    <x-input-label>Short Name</x-input-label>
    <x-text-input type="text" name="short_name" />
    <x-input-label>Size</x-input-label>
    <x-text-input type="text" name="size" />
    <br class="my-4">
    <x-primary-button>Create</x-primary-button>

</form>
    </section>
</x-app-layout>