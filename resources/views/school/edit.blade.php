<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <section  class="section-box">

<form action="{{route('school.update', $school->id)}}" method="post" class="nice-form">
    @csrf
    @method('put')
    <x-text-input type="text" name="name" placeholder="{{ __('messages.name') }}" value="{{old('name',$school->name)}}"/>
    <x-text-input type="text" name="code" placeholder="{{ __('messages.code') }}" value="{{old('code',$school->code)}}"/>
    <x-text-input class="mx-6" type="text" name="address2" id="address2" placeholder="{{ __('messages.address') }} 1" value="{{old('address2',$school->address2)}}"/>
    <x-text-input class="mx-6" type="text" name="address" id="address" placeholder="{{ __('messages.address') }} 2" value="{{old('address',$school->address)}}"/>
    <x-text-input class="mx-6" type="text" name="city" id="city" placeholder="{{ __('messages.city') }}" value="{{old('city',$school->city)}}"/>
    <x-text-input class="mx-6" type="text" name="zip" id="zip" placeholder="{{ __('messages.zip')}}" value="{{old('zip',$school->zip)}}"/>
    <x-text-input class="mx-6" type="text" name="country" id="country" placeholder="{{ __('messages.country') }}" value="{{old('country',$school->country)}}"/>
    <br class="my-4">
    <x-primary-button>{{ __('messages.update') }}</x-primary-button>
</form>
    </section>
</x-app-layout>