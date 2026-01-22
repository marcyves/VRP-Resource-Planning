<x-app-layout>
    @push('styles')
    @vite(['resources/css/groups.css'])
    @endpush

    <x-slot name="header">
        <h2>{{ __('messages.course_list') }} de {{ $group->name }}</h2>
    </x-slot>

    <section class="glass-background">
        <ul class="group-grid">
            @foreach ($courses as $course)
            <li class="group-card glass-background">
                {{ $course->name }}
            </li>
            @endforeach
        </ul>
    </section>
</x-app-layout>