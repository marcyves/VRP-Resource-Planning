<x-app-layout>
    <x-slot name="header">
        <h2>
            {{ __('Group Modification') }}
        </h2>
    </x-slot>

    @if(Auth::user()->getMode() == "Edit")
    <section>
        <form action="{{route('group.update', $group->id)}}" method="post"
            class="mx-auto px-6 py-2 bg-white shadow-md mb-6 flex flex-col gap-4">
            @csrf
            @method('put')

            <select name="course_id" class="bg-blue-200 border-blue-400 rounded-md px-4 py-2">
                @foreach($courses as $course)
                <option value={{$course->id}}>({{$course->school->name}}) {{$course->name}}</option>
                @endforeach
            </select>
            <x-text-input type="text" name="name" id="name" placeholder="{{ __('messages.name') }}" value="{{old('name', $group->name)}}" />
            @error('name')
            <div class="bg-red-700 text-red-100 border-red-400 rounded-md px-4 py-2">{{ $message }}</div>
            @enderror
            <x-text-input type="text" name="short_name" id="short_name" placeholder="{{ __('messages.short_name') }}" value="{{old('short_name', $group->short_name)}}" />
            @error('short_name')
            <div class="bg-red-700 text-red-100 border-red-400 rounded-md px-4 py-2">{{ $message }}</div>
            @enderror
            <x-text-input type="text" name="size" id="size" placeholder="{{ __('messages.size') }}" value="{{old('size', $group->size)}}" />
            @error('size')
            <div class="bg-red-700 text-red-100 border-red-400 rounded-md px-4 py-2">{{ $message }}</div>
            @enderror
            <x-text-input type="text" name="year" id="year" placeholder="{{ __('messages.year') }}" value="{{old('year', $group->year)}}" />
            @error('year')
            <div class="bg-red-700 text-red-100 border-red-400 rounded-md px-4 py-2">{{ $message }}</div>
            @enderror

            <x-button-primary>{{ __('messages.update') }}</x-button-primary>
        </form>
    </section>
    @endif

</x-app-layout>