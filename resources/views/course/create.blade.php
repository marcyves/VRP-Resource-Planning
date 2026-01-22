<x-app-layout>
    @push('styles')
    @vite(['resources/css/courses.css'])
    @endpush

    <x-slot name="header">
        <h2>{{ __('messages.course_create') }} {{$school->name}}</h2>
    </x-slot>

    <section class="glass-background">
        <form action="{{route('course.store', $school->id)}}" method="post" class="group-form glass-background-solid">
            @csrf

            <div class="form-group">
                <x-input-label for="name">Name</x-input-label>
                <x-text-input type="text" name="name" id="name" placeholder="Name" />
            </div>

            <div class="form-group">
                <x-input-label for="short_name">Short Name</x-input-label>
                <x-text-input type="text" name="short_name" id="short_name" placeholder="Short Name" />
            </div>

            <div class="form-group">
                <x-input-label for="program_id">Program</x-input-label>
                <select name="program_id" id="program_id" class="form-input">
                    @foreach ($programs as $program)
                    <option value="{{$program->id}}">{{$program->name}}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <x-input-label for="sessions">Number of sessions</x-input-label>
                <x-text-input type="text" name="sessions" id="sessions" placeholder="Number of sessions" />
            </div>

            <div class="form-group">
                <x-input-label for="session_length">Session length</x-input-label>
                <x-text-input type="text" name="session_length" id="session_length" placeholder="Session length" />
            </div>

            <div class="form-group">
                <x-input-label for="rate">Rate</x-input-label>
                <x-text-input type="text" name="rate" id="rate" placeholder="Rate" />
            </div>

            <div class="form-group">
                <x-input-label for="year">Year</x-input-label>
                <x-text-input type="text" name="year" id="year" value="{{now()->format('Y')}}" placeholder="Year" />
            </div>

            <div class="form-group">
                <x-input-label for="semester">Semester</x-input-label>
                <x-text-input type="text" name="semester" id="semester" placeholder="Semester" />
            </div>

            <div class="form-actions">
                <x-button-primary>Create</x-button-primary>
            </div>
        </form>
    </section>
</x-app-layout>