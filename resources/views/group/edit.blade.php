<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Group Modification') }}
        </h2>
    </x-slot>

    <section  class="nice-page">

<form action="{{route('group.update', $group->id)}}" method="post">
    @csrf
    @method('put')
    <x-input-label>Name</x-input-label>
    <x-text-input type="text" name="name" value="{{old('name', $group->name)}}"/>
    <x-input-label>Short Name</x-input-label>
    <x-text-input type="text" name="short_name" value="{{old('short_name', $group->short_name)}}"/>
    <x-input-label>Size</x-input-label>
    <x-text-input type="text" name="size" value="{{old('size', $group->size)}}"/>
    <br class="my-4">
    <x-primary-button>Update</x-primary-button>

</form>
    </section>
</x-app-layout>