<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <x-nice-box color="white">

<form action="{{route('course.store', $school_id)}}" method="post">
    @csrf
    <x-input-label>Name</x-input-label>
    <x-text-input type="text" name="name" />
    <x-input-label>Short Name</x-input-label>
    <x-text-input type="text" name="short_name" />
    <x-input-label>Program</x-input-label>
    <select name="program_id" class="rounded-md mt-0 py-0 pl-2 pr-8">
        @foreach ($programs as $program)
        <option value="{{$program->id}}">{{$program->name}}</option>                            
        @endforeach
    </select>
<x-input-label>Sessions</x-input-label>
    <x-text-input type="text" name="sessions" />
    <x-input-label>Session length</x-input-label>
    <x-text-input type="text" name="session_length" />
    <x-input-label>Rate</x-input-label>
    <x-text-input type="text" name="rate" />
    <x-input-label>Year</x-input-label>
    <x-text-input type="text" name="year" value="{{now()->format('Y')}}"/>
    <x-input-label>Semester</x-input-label>
    <x-text-input type="text" name="semester" />
    <br class="my-4">
    <x-primary-button>Create</x-primary-button>

</form>
    </x-nice-box>
</x-app-layout>