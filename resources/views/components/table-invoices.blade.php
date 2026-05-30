    @php
    $total = 0;
    $total_payed = 0;
    foreach ($invoices as $bill) {
        $total += $bill->amount;
        if ($bill->paid_at !== null) {
            $total_payed += $bill->amount;
        }
    }
    @endphp

    <x-kpi-grid :items="[
        ['icon' => 'wallet', 'label' => __('messages.total_gain'), 'value' => number_format($total, 2, ',', ' ') . ' €'],
        ['icon' => 'receipt', 'label' => __('messages.total_payed'), 'value' => number_format($total_payed, 2, ',', ' ') . ' €', 'variant' => 'success'],
        ['icon' => 'chart', 'label' => __('messages.total_balance'), 'value' => number_format($total - $total_payed, 2, ',', ' ') . ' €'],
    ]" />

    <div class="data-table">
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
                    <th>{{ __('messages.actions') }}</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($invoices as $bill)
                <tr>
                    <td>{{Auth::user()->company->bill_prefix}}{{$bill->id}}</td>
                    <td>{{$bill->school}}</td>
                    <td>{{$bill->description}}</td>
                    <td class="money">@money($bill->amount/1.2) €</td>
                    <td class="money">@money($bill->amount) €</td>
                    <td class="date">
                        @if($bill->created_at)
                        {{$bill->bill_date}}
                        @endif
                    </td>
                    <td>
                        <span class="status-chip invoice-e-status invoice-e-status--{{ $bill->electronic_invoice_status?->value ?? 'draft' }}">
                            {{ $bill->electronicStatusLabel() }}
                        </span>
                    </td>
                    <td class="date">
                        @if($bill->paid_at)
                            <span class="status-chip status-chip--paid">@formatDate($bill->paid_at)</span>
                        @else
                            <span class="status-chip status-chip--unpaid">{{ __('messages.not_payed') }}</span>
                        @endif
                    </td>
                    <td class="card-actions">
                        <a href="{{ route('invoice.show', $bill->id) }}" class="btn-icon icon icon--pdf-view" title="{{ __('messages.invoice_view') }}" aria-label="{{ __('messages.invoice_view') }}">
                            <img src="{{ asset('icons/pdf.png') }}" alt="" decoding="async">
                        </a>
                        @if (Auth::user()->getMode() == 'Edit')
                            @php
                            $isPaid = $bill->paid_at !== null;
                            @endphp
                            <form class="inline-form" action="{{ route('invoice.payed', $bill->id) }}" method="get">
                                <x-button-payed :paid="$isPaid" />
                            </form>
                            @if ($isPaid)
                                <button class="icon icon--edit icon--disabled" type="button" aria-label="{{ __('messages.invoice_paid_locked') }}" disabled>
                                    <img src="{{ asset('icons/edit.svg') }}" alt="" width="18" height="18" decoding="async">
                                </button>
                                <button class="icon icon--delete icon--disabled" type="button" aria-label="{{ __('messages.invoice_paid_locked') }}" disabled>
                                    <img src="{{ asset('icons/trash.svg') }}" alt="" width="18" height="18" decoding="async">
                                </button>
                            @else
                                <form class="inline-form" action="{{ route('invoice.edit', $bill->id) }}" method="get">
                                    <x-button-edit />
                                </form>
                                <form class="inline-form" action="{{ route('invoice.destroy', $bill->id) }}" method="post">
                                    @csrf
                                    @method('delete')
                                    <x-button-delete />
                                </form>
                            @endif
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
