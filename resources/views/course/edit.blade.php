<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Course Update') }}
        </h2>
    </x-slot>

    <section class="section-box">
        @isset($course)
            <form action="{{route('course.update', $course->id)}}" method="post" class="nice-form">
                @csrf
                @method('put')
                <div class="flex-row">
                    <x-input-label>Name</x-input-label>
                    <x-text-input type="text" name="name" value="{{old('name',$course->name)}}"/>
                </div>
                <div class="flex-row">
                    <x-input-label>Short Name</x-input-label>
                    <x-text-input type="text" name="short_name" value="{{old('short_name',$course->short_name)}}"/>
                </div>
                <div class="flex-row">
                    <x-input-label>Program</x-input-label>
                <select name="program_id" class="rounded-md mt-4 py-0 pl-2 pr-8">
                    @foreach ($programs as $program)
                    <option value="{{$program->id}}"
                        @if($program->id==$course->program_id)
                        selected
                        @endif
                        >{{$program->name}}</option>                            
                    @endforeach
                </select>
                </div>
                <div class="flex-row">
                <x-input-label>Sessions</x-input-label>
                <x-text-input type="text" name="sessions"  value="{{old('sessions',$course->sessions)}}"/>
                </div>
                <div class="flex-row">
                <x-input-label>Session length</x-input-label>
                <x-text-input type="text" name="session_length"  value="{{old('session_length',$course->session_length)}}"/>
                </div>
                <div class="flex-row">
                <x-input-label>Rate</x-input-label>
                <x-text-input type="text" name="rate"  value="{{old('rate',$course->rate)}}"/>
                </div>
                <div class="flex-row">
                <x-input-label>Year</x-input-label>
                <x-text-input type="text" name="year"  value="{{old('year',$course->year)}}"/>
                </div>
                <div class="flex-row">
                <x-input-label>Semester</x-input-label>
                <x-text-input type="text" name="semester"  value="{{old('semester',$course->semester)}}"/>
                </div>
                <div class="flex-row">
                <x-primary-button>{{ __('messages.update') }}</x-primary-button>
                </div>
            </form>
        @endisset
    </section>
</x-app-layout>