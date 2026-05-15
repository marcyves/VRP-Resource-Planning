<x-app-layout>
<x-slot name="header">
        <h2>{{ __('messages.course_list') }} de {{ $group->name }}</h2>
    </x-slot>

    <section>
        <ul class="group-grid">
            @foreach ($courses as $course)
            <li class="group-card">
                {{ $course->name }}
            </li>
            @endforeach
        </ul>
    </section>
</x-app-layout>