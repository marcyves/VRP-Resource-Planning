<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Course Update') }}
        </h2>
    </x-slot>

    <x-nice-box color="white">
        @isset($course)
            <form action="{{route('course.update', $course->id)}}" method="post">
                @csrf
                @method('put')
                <x-input-label>Name</x-input-label>
                <x-text-input type="text" name="name" value="{{old('name',$course->name)}}"/>
                <x-input-label>Program</x-input-label>
                <select name="program_id">
                    @foreach ($programs as $program)
                    <option value="{{$program->id}}"
                        @if($program->id==$course->program_id)
                        selected
                        @endif
                        >{{$program->name}}</option>                            
                    @endforeach
                </select>
                <x-input-label>Sessions</x-input-label>
                <x-text-input type="text" name="sessions"  value="{{old('sessions',$course->sessions)}}"/>
                <x-input-label>Session length</x-input-label>
                <x-text-input type="text" name="session_length"  value="{{old('session_length',$course->session_length)}}"/>
                <x-input-label>Rate</x-input-label>
                <x-text-input type="text" name="rate"  value="{{old('rate',$course->rate)}}"/>
                <x-input-label>Year</x-input-label>
                <x-text-input type="text" name="year"  value="{{old('year',$course->year)}}"/>
                <x-input-label>Semester</x-input-label>
                <x-text-input type="text" name="semester"  value="{{old('semester',$course->semester)}}"/>
                <br class="my-4">
                <x-primary-button>Save</x-primary-button>
            </form>
        @endisset
    </x-nice-box>
</x-app-layout>