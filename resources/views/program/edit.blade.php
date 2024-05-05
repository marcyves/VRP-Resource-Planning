<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Program Modification') }}
        </h2>
    </x-slot>

    <section  class="nice-page">

<form action="{{route('program.update', $program->id)}}" method="post">
    @csrf
    @method('put')
    <x-input-label>Name</x-input-label>
    <x-text-input type="text" name="name" value="{{old('name',$program->name)}}"/>
    <br class="my-4">
    <x-primary-button>Modify</x-primary-button>
</form>
    </section>
</x-app-layout>