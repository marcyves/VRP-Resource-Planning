<x-app-layout>
    <x-slot name="header">
    <div class="flex">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight grow py-2">
            {{ __('Program Details')}} : {{$program->name}}
        </h2>
        @if(Auth::user()->getMode() == "Edit")
    <div class="flex flex-col md:flex-row space-y-2 md:space-y-0 items-stretch md:items-center justify-end md:space-x-3 flex-shrink-0">
        <div class="flex items-center space-x-3 w-full md:w-auto">
            <form action="{{route('program.edit', $program->id)}}" method="get">
<x-button-edit/>
            </form>
            <form action="{{route('program.destroy', $program->id)}}" method="post">
                @csrf
                @method('delete')
<x-button-delete/>
            </form>
            @if(session('school_id') !== null)
            <a
            class="inline-flex items-center p-2 text-sm border border-gray-300 rounded-md font-semibold font-medium text-center text-gray-500 hover:text-gray-800 rounded-lg focus:outline-none" 
            href="{{route('course.create', session('school_id'))}}">{{__('messages.add_course')}}</a>
            @endif
        </div>
    </div>
    @endif
</div>
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