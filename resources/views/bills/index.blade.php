<x-app-layout>
    <x-slot name="header">
            <h2>
                {{ __('messages.bills') }}
            </h2>
    </x-slot>

    <section class="bills">
        <table>
            @php
            $total = 0;
            $total_payed = 0;
            @endphp
            <thead>
                <tr class="text-center">
                    <th>
                        {{ __('messages.bill_id') }}
                    </th>
                    <th>
                        {{ __('messages.description') }}
                    </th>
                    <th>
                        {{ __('messages.amount') }}
                    </th>
                    <th>
                        {{ __('messages.date_billing') }}
                    </th>
                    <th>
                        {{ __('messages.date_payment') }}
                    </th>
                    @if(Auth::user()->getMode() == "Edit")
                    <th>
                        {{ __('messages.actions') }}
                    </th>
                    @endif
                </tr>
            </thead>
            @foreach ($bills as $bill)
            <tr>
                <td>
                    {{$bill->id}}
                </td>
                <td>
                    {{$bill->description}}
                </td>
                <td class="money">
                    @money($bill->amount) €
                    @php
                    $total += $bill->amount;
                    if($bill->paid_at != null){
                        $total_payed += $bill->amount;
                    }
                    @endphp
                </td>
                <td class="date">
                    @if($bill->created_at)
                    {{$bill->created_at->format('d/m/Y')}}
                    @endif
                </td>
                <td class="date">
                    @if($bill->paid_at)
                     @formatDate($bill->paid_at)
                    @else
                    {{ __('messages.not_payed') }}
                    @endif
                </td>
                @if(Auth::user()->getMode() == "Edit")
                <td>
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

        <div class="card">
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
    <section>
    <form action="{{route('bill.store')}}" method="post"
        class="bills-form">
            @csrf
            <x-input-label class="my-4">{{ __('messages.bill_id') }}: {{$bill_id}}</x-input-label> 
                <input type="hidden" name="id" id="name" value="{{$bill_id}}">
                <x-text-input type="text" name="description" id="description" size="60" placeholder="{{ __('messages.description') }}"/>
                <div>
                <x-text-input class="my-2" type="text" name="amount" id="amount" size="20" placeholder="{{ __('messages.amount') }}"/>
                <x-primary-button>{{ __('messages.bill_create') }}</x-primary-button>
                </div>
        </form>
    </section>
    @endif

</x-app-layout>