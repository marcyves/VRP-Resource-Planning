<x-app-layout>
    @push('styles')
    @vite(['resources/css/programs.css'])
    @endpush

    <x-slot name="header">
        <div class="header-content">
            <h2 class="header-title">
                {{ __('Program Details')}} : {{$program->name}}
            </h2>
            @if(Auth::user()->getMode() == "Edit")
            <div class="header-actions">
                <form action="{{route('program.edit', $program->id)}}" method="get">
                    <x-button-edit />
                </form>
                <form action="{{route('program.destroy', $program->id)}}" method="post">
                    @csrf
                    @method('delete')
                    <x-button-delete />
                </form>
                @if(session('school_id') !== null)
                <a class="btn-secondary" href="{{route('course.create', session('school_id'))}}">{{__('messages.add_course')}}</a>
                @endif
            </div>
            @endif
        </div>
    </x-slot>

    <section class="program-list-section glass-background">
        <h2 class="card-subtitle mb-4">{{__('messages.course_list')}}</h2>
        <div class="program-course-grid">
            @foreach($courses as $course)
            <div class="program-course-item glass-background">
                <a class="nav-link font-bold" href="{{route('course.show', $course->id)}}">
                    {{$course->name}}
                </a>
            </div>
            @endforeach
        </div>
    </section>
</x-app-layout>