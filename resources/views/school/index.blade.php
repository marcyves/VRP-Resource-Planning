<x-app-layout>
    <x-slot name="header">
        <h2>
            {{ __('messages.schools_list') }}
        </h2>
        @if(Auth::user()->getMode() == "Edit")
            <a class="p-2 text-sm border border-gray-300 rounded-md font-semibold font-medium text-center text-gray-500 hover:text-gray-800 rounded-lg focus:outline-none" 
                href="{{route('school.list')}}"> {{ __('messages.school_no_course') }}</a>
        @endif
    </x-slot>
 
    <section>
        <ul class="list">
            @foreach ($schools as $school)
            <li class="card">
                <x-school-header :school_name="$school->name" :school_id="$school->id"/>
            </li>
            @endforeach
        </ul>  
    </section>

    @if(Auth::user()->getMode() == "Edit")
    <section>
        <form action="{{route('school.store')}}" method="post" 
        class="mx-auto px-6 py-2 bg-white shadow-md mb-6 flex items-center justify-items-start">
            @csrf
            <x-text-input class="mx-6" type="text" name="name" id="name" placeholder="{{ __('messages.name') }}" value="{{old('name')}}"/>
            <x-text-input class="mx-6" type="text" name="code" id="code" placeholder="{{ __('messages.code') }}" value="{{old('code')}}"/>
            <x-text-input class="mx-6" type="text" name="address" id="address" placeholder="{{ __('messages.address') }}" value="{{old('address')}}"/>
            <x-text-input class="mx-6" type="text" name="city" id="city" placeholder="{{ __('messages.city') }}" value="{{old('city')}}"/>  
            <x-text-input class="mx-6" type="text" name="zip" id="zip" placeholder="{{ __('messages.zip') }}"/>
            <x-text-input class="mx-6" type="text" name="country" id="country" placeholder="{{ __('messages.country') }}" value="{{old('country', 'France')}}"/>
            <x-primary-button>{{ __('messages.school_create') }}</x-primary-button>
        </form>
    </section>
    @endif

</x-app-layout>