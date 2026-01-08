<x-app-layout>
    <x-slot name="header">
        <h2>
            {{ __('messages.course_create') }} {{$school->name}}
        </h2>
    </x-slot>

    <section>
        <form action="{{route('course.store', $school->id)}}" method="post" class="nice-form">
            @csrf
            <x-text-input type="text" name="name" aria-placeholder="Name" placeholder="Name" />
            <x-text-input type="text" name="short_name" aria-placeholder="Short Name" placeholder="Short Name" />
            <div class="flex-row">
                <label>Program</label>
                <select name="program_id" class="rounded-md mt-0 py-0 pl-2 pr-8">
                    @foreach ($programs as $program)
                    <option value="{{$program->id}}">{{$program->name}}</option>
                    @endforeach
                </select>
            </div>
            <x-text-input type="text" name="sessions" aria-placeholder="Number of sessions" placeholder="Number of sessions" />
            <x-text-input type="text" name="session_length" aria-placeholder="Session length" placeholder="Session length" />
            <x-text-input type="text" name="rate" aria-placeholder="Rate" placeholder="Rate" />
            <x-text-input type="text" name="year" value="{{now()->format('Y')}}" aria-placeholder="Year" placeholder="Year" />
            <x-text-input type="text" name="semester" aria-placeholder="Semester" placeholder="Semester" />
            <x-button-primary>Create</x-button-primary>
        </form>
    </section>
</x-app-layout>