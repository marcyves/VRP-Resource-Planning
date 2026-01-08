<x-app-layout>
    <x-slot name="header">
        <h2>
            {{ __('Program Modification') }}
        </h2>
    </x-slot>

    <section>

        <form action="{{route('program.update', $program->id)}}" method="post">
            @csrf
            @method('put')
            <x-input-label>Name</x-input-label>
            <x-text-input type="text" name="name" value="{{old('name',$program->name)}}" />
            <br class="my-4">
            <x-button-primary>{{ __('messages.update') }}</x-button-primary>
        </form>
    </section>
</x-app-layout>