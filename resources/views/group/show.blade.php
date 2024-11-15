<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('messages.course_list') }} de {{$group->name}}
        </h2>
    </x-slot>

    <section  class="section-box">
        <ul>
        @foreach ($courses as $course)
            <li class="mx-auto max-w-screen-xl px-2 lg:px-12 bg-white shadow-md sm:rounded-lg overflow-hidden mb-2
            flex flex-row md:flex-col justify-between space-y-3 md:space-y-0 md:space-x-4 p-2">
            {{$course->name}}
            </li>
        @endforeach
        </ul>
    </section>

</x-app-layout>