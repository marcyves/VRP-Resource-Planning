<x-app-layout>
    @push('styles')
    @vite(['resources/css/groups.css'])
    @endpush

    <x-slot name="header">
        <h2>{{ __('messages.group_edit') }}</h2>
    </x-slot>

    @if(Auth::user()->getMode() == "Edit")
    <section class="glass-background">
        <form action="{{route('group.update', $group->id)}}" method="post" class="group-form glass-background-solid">
            @csrf
            @method('put')

            <div class="form-group">
                <select name="course_id" class="form-input">
                    @foreach($courses as $course)
                    <option value="{{$course->id}}" {{ $group->course_id == $course->id ? 'selected' : '' }}>
                        ({{$course->school->name}}) {{$course->name}}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <x-text-input type="text" name="name" id="name" placeholder="{{ __('messages.name') }}" value="{{old('name', $group->name)}}" />
                <x-input-error :messages="$errors->get('name')" />
            </div>

            <div class="form-group">
                <x-text-input type="text" name="short_name" id="short_name" placeholder="{{ __('messages.short_name') }}" value="{{old('short_name', $group->short_name)}}" />
                <x-input-error :messages="$errors->get('short_name')" />
            </div>

            <div class="form-group">
                <x-text-input type="text" name="size" id="size" placeholder="{{ __('messages.size') }}" value="{{old('size', $group->size)}}" />
                <x-input-error :messages="$errors->get('size')" />
            </div>

            <div class="form-group">
                <x-text-input type="text" name="year" id="year" placeholder="{{ __('messages.year') }}" value="{{old('year', $group->year)}}" />
                <x-input-error :messages="$errors->get('year')" />
            </div>

            <x-button-primary>{{ __('messages.update') }}</x-button-primary>
        </form>
    </section>
    @endif
</x-app-layout>