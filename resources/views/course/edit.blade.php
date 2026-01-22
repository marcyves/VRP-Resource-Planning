<x-app-layout>
    @push('styles')
    @vite(['resources/css/courses.css'])
    @endpush

    <x-slot name="header">
        <h2>{{ __('Course Update') }}</h2>
    </x-slot>

    <section class="glass-background">
        @isset($course)
        <form action="{{route('course.update', $course->id)}}" method="post" class="group-form glass-background-solid">
            @csrf
            @method('put')

            <div class="form-group">
                <x-input-label for="name">Name</x-input-label>
                <x-text-input type="text" name="name" id="name" value="{{old('name',$course->name)}}" />
            </div>

            <div class="form-group">
                <x-input-label for="short_name">Short Name</x-input-label>
                <x-text-input type="text" name="short_name" id="short_name" value="{{old('short_name',$course->short_name)}}" />
            </div>

            <div class="form-group">
                <x-input-label for="program_id">Program</x-input-label>
                <select name="program_id" id="program_id" class="form-input">
                    @foreach ($programs as $program)
                    <option value="{{$program->id}}" @if($program->id==$course->program_id) selected @endif>
                        {{$program->name}}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <x-input-label for="sessions">Sessions</x-input-label>
                <x-text-input type="text" name="sessions" id="sessions" value="{{old('sessions',$course->sessions)}}" />
            </div>

            <div class="form-group">
                <x-input-label for="session_length">Session length</x-input-label>
                <x-text-input type="text" name="session_length" id="session_length" value="{{old('session_length',$course->session_length)}}" />
            </div>

            <div class="form-group">
                <x-input-label for="rate">Rate</x-input-label>
                <x-text-input type="text" name="rate" id="rate" value="{{old('rate',$course->rate)}}" />
            </div>

            <div class="form-group">
                <x-input-label for="year">Year</x-input-label>
                <x-text-input type="text" name="year" id="year" value="{{old('year',$course->year)}}" />
            </div>

            <div class="form-group">
                <x-input-label for="semester">Semester</x-input-label>
                <x-text-input type="text" name="semester" id="semester" value="{{old('semester',$course->semester)}}" />
            </div>

            <div class="form-group">
                <x-input-label for="recurring">Recurring</x-input-label>
                <input type="checkbox" name="recurring" id="recurring" value="1" @if($course->recurring) checked @endif class="form-checkbox" />
            </div>

            <div class="form-actions">
                <x-button-primary>{{ __('messages.update') }}</x-button-primary>
            </div>
        </form>
        @endisset
    </section>
</x-app-layout>