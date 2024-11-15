<x-app-layout>
    <x-slot name="header">
            <h2>
                {{ __('messages.bills') }}
            </h2>
    </x-slot>

    <section class="section-box">
        <table class="bg-blue-100 w-full mb-8">
            @php
            $total = 0;
            $total_payed = 0;
            @endphp
            <tr class="bg-white text-blue-600 p-8">
                <td class="text-center px-2">
                ID
                </td>
                <td class="text-center px-2">
                    Description
                </td>
                <td class=" text-center px-2">
                    Montant
                </td>
                <td class=" text-center px-2">
                    Date facturation
                </td>
                <td class=" text-center px-2">
                    Date Paiement
                </td>
                @if(Auth::user()->getMode() == "Edit")
                <td class=" text-center px-2">
                    Actions
                </td>
                @endif
</tr>
            @foreach ($bills as $bill)
            <tr>
                <td class="basis-20  text-right text-gray-600 pr-4">
                    {{$bill->id}}
                </td>
                <td class="grow text-left  text-blue-400">
                    {{$bill->description}}
                </td>
                <td class=" text-right  text-blue-400 pr-4">
                    @money($bill->amount) €
                    @php
                    $total += $bill->amount;
                    if($bill->paid_at != null){
                        $total_payed += $bill->amount;
                    }
                    @endphp
                </td>
                <td class=" text-left  text-gray-400">
                    {{$bill->created_at}}
                </td>
                <td class=" text-left  text-gray-400">
                    Paid: {{$bill->paid_at}}
                </td>
                @if(Auth::user()->getMode() == "Edit")
                <td class=" text-right">
                    <form class="inline" action="{{route('bill.payed', $bill->id)}}" method="get">
                    <x-button-payed/>
                    </form>
                    <form class="inline" action="{{route('bill.edit', $bill->id)}}" method="get">
                    <x-button-edit/>
                    </form>
                    <form class="inline" action="{{route('bill.destroy', $bill->id)}}" method="post">
                        @csrf
                        @method('delete')
                        <x-button-delete />    
                    </form>
                </td>
                @endif
</tr>
            @endforeach
</table>

        <div class="my-box course-booking">
            <div class="flex flex-row items-center justify-between space-y-3 md:space-y-0 md:space-x-4 p-4">
            {{ __('messages.total_gain')}} : @money($total) €
            </div>
            <div class="flex flex-row items-center justify-between space-y-3 md:space-y-0 md:space-x-4 p-4">
            {{ __('messages.total_payed')}} : @money($total_payed) €
            </div>
            <div class="flex flex-row items-center justify-between space-y-3 md:space-y-0 md:space-x-4 p-4">
            {{ __('messages.total_balance')}} : @money($total - $total_payed) €
            </div>
        </div>  
</section>

    @if(Auth::user()->getMode() == "Edit")
    <section class="section-box">
    <form action="{{route('bill.store')}}" method="post"
        class="mx-auto px-6 py-2 bg-white shadow-md mb-6 flex flex-col justify-items-start">
            @csrf
            <x-input-label class="py-4">{{ __('messages.bill_id') }}: {{$bill_id}}</x-input-label> 
                <input type="hidden" name="id" id="name" value="{{$bill_id}}">
                <x-input-label >{{ __('messages.description') }}</x-input-label>
                <x-text-input class="my-4" type="text" name="description" id="description" size="60"/>
                <x-input-label>{{ __('messages.gain') }}</x-input-label>
                <x-text-input class="my-4" type="text" name="amount" id="amount" size="20"/>
                <x-primary-button>{{ __('messages.bill_create') }}</x-primary-button>
        </form>
    </section>
    @endif

</x-app-layout>