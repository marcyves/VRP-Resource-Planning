@props([
    'invoices',
    'sort' => 'id',
    'direction' => 'desc',
    'filters' => [],
])

@php
    $sortLink = function (string $column) use ($sort, $direction, $filters) {
        $nextDirection = ($sort === $column && $direction === 'asc') ? 'desc' : 'asc';

        return route('treasury.invoices.index', array_merge($filters, [
            'sort' => $column,
            'direction' => $nextDirection,
        ]));
    };
@endphp

<div class="data-table invoice-table">
    <table>
        <thead>
            <tr>
                <th class="invoice-table__sortable">
                    <a href="{{ $sortLink('id') }}" @class(['invoice-table__sort-link', 'invoice-table__sort-link--active' => $sort === 'id'])>
                        {{ __('messages.invoice_id') }}
                        @if ($sort === 'id')
                            <span class="invoice-table__sort-indicator" aria-hidden="true">{{ $direction === 'asc' ? '↑' : '↓' }}</span>
                        @endif
                    </a>
                </th>
                <th class="invoice-table__sortable">
                    <a href="{{ $sortLink('school') }}" @class(['invoice-table__sort-link', 'invoice-table__sort-link--active' => $sort === 'school'])>
                        {{ __('messages.school') }}
                        @if ($sort === 'school')
                            <span class="invoice-table__sort-indicator" aria-hidden="true">{{ $direction === 'asc' ? '↑' : '↓' }}</span>
                        @endif
                    </a>
                </th>
                <th class="invoice-table__sortable">
                    <a href="{{ $sortLink('description') }}" @class(['invoice-table__sort-link', 'invoice-table__sort-link--active' => $sort === 'description'])>
                        {{ __('messages.description') }}
                        @if ($sort === 'description')
                            <span class="invoice-table__sort-indicator" aria-hidden="true">{{ $direction === 'asc' ? '↑' : '↓' }}</span>
                        @endif
                    </a>
                </th>
                <th>{{ __('messages.amount') }} HT</th>
                <th>{{ __('messages.amount') }} TTC</th>
                <th>{{ __('messages.date_billing') }}</th>
                <th>{{ __('messages.electronic_invoice_status') }}</th>
                <th>{{ __('messages.date_payment') }}</th>
                <th>{{ __('messages.actions') }}</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($invoices as $bill)
            <tr>
                <td>{{ Auth::user()->company->bill_prefix }}{{ $bill->id }}</td>
                <td>{{ $bill->school }}</td>
                <td>{{ $bill->description }}</td>
                <td class="money">@money($bill->amount/1.2) €</td>
                <td class="money">@money($bill->amount) €</td>
                <td class="date">
                    @if($bill->bill_date)
                        {{ $bill->bill_date }}
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
                    <a href="{{ route('invoice.show', $bill->id) }}" class="icon icon--pdf-view" title="{{ __('messages.invoice_view') }}" aria-label="{{ __('messages.invoice_view') }}">
                        <x-icon-pdf-view />
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
            @empty
            <tr>
                <td colspan="9" class="invoice-table__empty">{{ __('messages.invoice_list_empty') }}</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
