<x-app-layout>
    @push('styles')
    @vite(['resources/css/groups.css'])
    @endpush

    <x-slot name="header">
        <h2>{{ __('messages.group_create') }}</h2>
    </x-slot>

    <section class="glass-background">
        <form action="{{route('group.save', $course_id)}}" method="post" class="group-form glass-background-solid">
            @csrf
            <x-form-group-create />
            <x-button-primary>Create</x-button-primary>
        </form>
    </section>
</x-app-layout>