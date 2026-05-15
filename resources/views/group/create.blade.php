<x-app-layout>
<x-slot name="header">
        <h2>{{ __('messages.group_create') }}</h2>
    </x-slot>

    <section>
        <form action="{{route('group.save', $course_id)}}" method="post" class="group-form">
            @csrf
            <x-form-group-create />
            <x-button-primary>Create</x-button-primary>
        </form>
    </section>
</x-app-layout>