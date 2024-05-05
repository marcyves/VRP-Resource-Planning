<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Program Details')}} : {{$program->name}}
        </h2>
    </x-slot>

    <section  class="nice-page">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-2">
            {{__('messages.course_list')}}
        </h2>
        <ul>
        @foreach($courses as $course)
        <li class="mx-auto max-w-screen-xl px-2 lg:px-12 bg-white shadow-md sm:rounded-lg overflow-hidden mb-2
            flex flex-col md:flex-row items-center justify-between space-y-3 md:space-y-0 md:space-x-4 p-2">
            <form action="{{route('course.show', $course->id)}}" method="get">
                @csrf
                <button class="inline-flex items-center p-0.5 text-sm font-medium text-center text-blue-500 hover:text-gray-800 rounded-lg focus:outline-none" type="submit">
                    {{$course->name}}
                </button>    
            </form>
        </li>
        @endforeach
        </ul>
     </section>
</x-app-layout>