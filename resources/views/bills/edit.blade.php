<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row items-center justify-between space-y-3 md:space-y-0 md:space-x-4 p-4">
            <h2 class="w-full md:w-1/2 inline-flex font-semibold text-xl text-gray-800">
                {{ __('messages.bills') }}
            </h2>
    </x-slot>

    @if(Auth::user()->getMode() == "Edit")
    <section>
    <form action="{{route('bill.update', $bill)}}" method="post"
        class="mx-auto px-6 py-2 bg-white shadow-md mb-6 flex flex-col justify-items-start">
            @csrf
            @method('patch')
            <x-input-label class="py-4">{{ __('messages.bill_id') }}: {{$bill->id}}</x-input-label> 
                <input type="hidden" name="id" id="name" value="{{$bill->id}}">
                <x-input-label >{{ __('messages.description') }}</x-input-label>
                <x-text-input class="my-4" type="text" name="description" id="description" size="60" value="{{ $bill->description }}"/>
                <x-input-label>{{ __('messages.gain') }}</x-input-label>
                <x-text-input class="my-4" type="text" name="amount" id="amount" size="20" value="{{ $bill->amount }}"/>
                <x-input-label>{{ __('messages.created_at') }}</x-input-label>
                <x-text-input class="my-4" type="datetime-local" name="created_at" id="created_at" size="20" value="{{ $bill->created_at }}"/>
                <x-input-label>{{ __('messages.paid_at') }}</x-input-label>
                <x-text-input class="my-4" type="datetime-local" name="paid_at" id="paid_at" size="20" value="{{ $bill->paid_at }}"/>
                <x-primary-button>{{ __('messages.update') }}</x-primary-button>
        </form>
    </section>
    @endif

</x-app-layout>