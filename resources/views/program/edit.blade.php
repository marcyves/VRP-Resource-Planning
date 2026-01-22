<x-app-layout>
    @push('styles')
    @vite(['resources/css/programs.css'])
    @endpush

    <x-slot name="header">
        <h2>{{ __('Program Modification') }}</h2>
    </x-slot>

    <section class="glass-background">
        <form action="{{route('program.update', $program->id)}}" method="post" class="group-form glass-background-solid">
            @csrf
            @method('put')

            <div class="form-group">
                <x-input-label for="name">Name</x-input-label>
                <x-text-input type="text" name="name" id="name" value="{{old('name',$program->name)}}" />
            </div>

            <div class="form-actions">
                <x-button-primary>{{ __('messages.update') }}</x-button-primary>
            </div>
        </form>
    </section>
</x-app-layout>