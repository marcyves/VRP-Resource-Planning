<x-app-layout>
    <x-slot name="header">
        <h2>
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <section>
        <form action="{{route('group.save', $course_id)}}" method="post">
            @csrf
            <x-form-group-create />
            <br class="my-4">
            <x-button-primary>Create</x-button-primary>
        </form>
    </section>
</x-app-layout>