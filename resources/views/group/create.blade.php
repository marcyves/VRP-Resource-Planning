<x-app-layout>
    <x-slot name="header">
        <h2>
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <section >
        <form action="{{route('group.save', $course_id)}}" method="post">
            @csrf
            <x-form-group-create/>
            <br class="my-4">
            <x-primary-button>Create</x-primary-button>
        </form>
    </section>
</x-app-layout>