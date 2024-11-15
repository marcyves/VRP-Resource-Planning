<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('messages.course_create') }} {{$school->name}}
        </h2>
    </x-slot>

    <section class="section-box">
    <form action="{{route('course.store', $school->id)}}" method="post" class="nice-form">
        @csrf
        <div class="flex-row">
            <x-input-label>Name</x-input-label><x-text-input type="text" name="name" />
        </div>
        <div class="flex-row">
            <x-input-label>Short Name</x-input-label><x-text-input type="text" name="short_name" />
        </div>
        <div class="flex-row">
            <x-input-label>Program</x-input-label>
        <select name="program_id" class="rounded-md mt-0 py-0 pl-2 pr-8">
            @foreach ($programs as $program)
            <option value="{{$program->id}}">{{$program->name}}</option>                            
            @endforeach
        </select>
        </div>
        <div class="flex-row">
            <x-input-label>Sessions</x-input-label><x-text-input type="text" name="sessions" />
        </div>
        <div class="flex-row">
            <x-input-label>Session length</x-input-label><x-text-input type="text" name="session_length" />
        </div>
        <div class="flex-row">
            <x-input-label>Rate</x-input-label><x-text-input type="text" name="rate" />
        </div>
        <div class="flex-row">
            <x-input-label>Year</x-input-label><x-text-input type="text" name="year" value="{{now()->format('Y')}}"/>
        </div>
        <div class="flex-row">
            <x-input-label>Semester</x-input-label><x-text-input type="text" name="semester" />
        </div>
        <div class="flex-row">
            <x-primary-button>Create</x-primary-button>
        </div>
    </form>
</section>
</x-app-layout>