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
                    <th>{{ __('messages.electronic_invoice_status') }}</th>
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
                    <td class="money">@money($bill->amount/1.2) €</td>
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
                        {{$bill->bill_date}}
                        @endif
                    </td>
                    <td>
                        <span class="invoice-e-status invoice-e-status--{{ $bill->electronic_invoice_status?->value ?? 'draft' }}">
                            {{ $bill->electronicStatusLabel() }}
                        </span>
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
                        @php
                        $isPaid = $bill->paid_at !== null;
                        @endphp
                        <a href="{{route('invoice.show', $bill->id)}}" class="btn-icon" title="{{ __('messages.view') }}">
                            <x-button-view />
                        </a>
                        <form class="inline-form" action="{{route('invoice.payed', $bill->id)}}" method="get">
                            <x-button-payed :paid="$isPaid" />
                        </form>
                        @if($isPaid)
                        <button class="icon icon--edit icon--disabled" type="button" aria-label="{{ __('messages.invoice_paid_locked') }}" disabled>
                            <img src="{{ asset('icons/edit.svg') }}" alt="" width="18" height="18" decoding="async">
                        </button>
                        <button class="icon icon--delete icon--disabled" type="button" aria-label="{{ __('messages.invoice_paid_locked') }}" disabled>
                            <img src="{{ asset('icons/trash.svg') }}" alt="" width="18" height="18" decoding="async">
                        </button>
                        @else
                            <form class="inline-form" action="{{route('invoice.edit', $bill->id)}}" method="get">
                                <x-button-edit />
                            </form>
                            <form class="inline-form" action="{{route('invoice.destroy', $bill->id)}}" method="post">
                                @csrf
                                @method('delete')
                                <x-button-delete />
                            </form>
                        @endif
                    </td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="summary-container">
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