<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <section  class="section-box">

<form action="{{route('school.update', $school->id)}}" method="post">
    @csrf
    @method('put')
    <x-input-label>Name</x-input-label>
    <x-text-input type="text" name="name" value="{{old('name',$school->name)}}"/>
            <x-text-input class="mx-6" type="text" name="address" id="address" placeholder="{{ __('messages.address') }}" value="{{old('address',$school->address)}}"/>
            <x-text-input class="mx-6" type="text" name="city" id="city" placeholder="{{ __('messages.city') }}" value="{{old('city',$school->city)}}"/>
            <x-text-input class="mx-6" type="text" name="zip" id="zip" placeholder="{{ __('messages.zip')}}" value="{{old('zip',$school->zip)}}"/>
            <x-text-input class="mx-6" type="text" name="country" id="country" placeholder="{{ __('messages.country') }}" value="{{old('country',$school->country)}}"/>
       <br class="my-4">
    <x-primary-button>{{ __('messages.update') }}</x-primary-button>
</form>
    </section>
</x-app-layout>