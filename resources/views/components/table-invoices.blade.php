    @php
    $total = 0;
    $total_payed = 0;
    @endphp

    <div class="bills">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.invoice_id') }}</th>
                    <th>{{ __('messages.school') }}</th>
                    <th>{{ __('messages.description') }}</th>
                    <th>{{ __('messages.amount') }} HT</th>
                    <th>{{ __('messages.amount') }} TTC</th>
                    <th>{{ __('messages.date_billing') }}</th>
                    <th>{{ __('messages.date_payment') }}</th>
                    @if(Auth::user()->getMode() == "Edit")
                    <th>{{ __('messages.actions') }}</th>
                    @endif
                </tr>
            </thead>

            <tbody>
                @foreach ($invoices as $bill)
                <tr>
                    <td>{{Auth::user()->company->bill_prefix}}{{$bill->id}}</td>
                    <td>{{$bill->school}}</td>
                    <td>{{$bill->description}}</td>
                    <td class="money">@money($bill->amount) €</td>
                    <td class="money">
                        @money($bill->amount*1.2) €
                        @php
                        $total += $bill->amount*1.2;
                        if($bill->paid_at != null){
                        $total_payed += $bill->amount*1.2;
                        }
                        @endphp
                    </td>
                    <td class="date">
                        @if($bill->created_at)
                        {{$bill->bill_date}}
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
                    <td class="card-actions">
                        <a href="{{route('invoice.show', $bill->id)}}" class="btn-icon" title="{{ __('messages.view') }}">
                            <x-button-view />
                        </a>
                        <form class="inline-form" action="{{route('invoice.payed', $bill->id)}}" method="get">
                            <x-button-payed />
                        </form>
                        <form class="inline-form" action="{{route('invoice.edit', $bill->id)}}" method="get">
                            <x-button-edit />
                        </form>
                        <form class="inline-form" action="{{route('invoice.destroy', $bill->id)}}" method="post">
                            @csrf
                            @method('delete')
                            <x-button-delete />
                        </form>
                    </td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="summary-container glass-background">
        <div class="summary-item">
            <span>{{ __('messages.total_gain')}} :</span>
            <strong>@money($total)</strong>
        </div>
        <div class="summary-item">
            <span>{{ __('messages.total_payed')}} :</span>
            <strong>@money($total_payed)</strong>
        </div>
        <div class="summary-item total">
            <span>{{ __('messages.total_balance')}} :</span>
            <strong>@money($total - $total_payed)</strong>
        </div>
    </div>