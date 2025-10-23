<x-app-layout>
    <x-slot name="header">
        <div class="flex">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight grow py-2">
                {{__('messages.school_details')}}
            </h2>
        </div>
    </x-slot>

    <section class="glass-background">
        @php
            $total_time = 0;
            $total_budget = 0;
            $school_name = $school->name;
            $school_id = $school->id;
        @endphp
        <article class="school-box">
        <x-school-header :school_name=$school_name :school_id=$school_id />
        <x-course-table :courses=$courses :school_name=$school_name :school_id=$school_id/>
    </article>
    </section>

    <section class="glass-background">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">{{ __('messages.address') }}</h3>

            <div class="flex items-center">
                {{  $school->address }}<br>
                {{  $school->city }}, {{  $school->zip }}<br>
                {{  $school->country }}<br>
                @if($school->phone)
                    {{ __('messages.phone') }}: {{  $school->phone }}<br>
                @endif
            </div>
        </div>
    </section>

    <section class="glass-background">
        <h2>Invoices</h2>

        <div class="bills">
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
                        {{ __('messages.school') }}
                    </th>
                    <th>
                        {{ __('messages.description') }}
                    </th>
                    <th>
                        {{ __('messages.amount') }} HT
                    </th>
                    <th>
                        {{ __('messages.amount') }} TTC
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
            @foreach ($invoices as $bill)
            <tr>
                <td>
                    {{Auth::user()->company->bill_prefix}}{{$bill->id}}
                </td>
                <td>
                    {{$bill->school}}
                <td>
                    {{$bill->description}}
                </td>
                <td class="money">
                    @money($bill->amount) €
                </td>
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
                <td>
                    <a href="{{route('invoice.show', $bill->id)}}">
                        <x-button-view/>
                    </a>
                    <form class="inline" action="{{route('invoice.payed', $bill->id)}}" method="get">
                    <x-button-payed/>
                    </form>
                    <form class="inline" action="{{route('invoice.edit', $bill->id)}}" method="get">
                    <x-button-edit/>
                    </form>
                    <form class="inline" action="{{route('invoice.destroy', $bill->id)}}" method="post">
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
        </div>
    </section>

    <section class="glass-background">
        <x-documents-school-table :documents=$documents :school_id=$school_id/>
    <div class="mx-auto max-w-screen-xl px-2 lg:px-12">
        <div class="relative shadow-md sm:rounded-lg overflow-hidden">
            <div class="flex flex-col md:flex-row items-center justify-between space-y-3 md:space-y-0 md:space-x-4 p-4">
                <div class="w-full text-center text-gray-600">
                    <form action="{{route('document.store', $school_id)}}" 
                        class="flex justify-between" 
                        method="post"
                        enctype="multipart/form-data">
                    @csrf
                    <div class="m-2 mx-6">
                        <input type="text" name="description" id="desc" 
                               class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mb-6"
                               placeholder="Document Description">
                        @error('description')
                            <div class="text-red-500" role="alert">
                                {{ $message }}
                            </div>
                        @enderror
                        <input type="text" name="year" id="desc" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" value="{{date('Y')}}">
                        @error('year')
                            <div class="text-red-500" role="alert">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="my-4">
                        <input type="file" class="form-control" name="document" @error('document') is-invalid @enderror>
                        @error('document')
                            <div class="text-red-500" role="alert">
                                {{ $message }}
                            </div>
                        @enderror
                        <button class="items-center p-0.5 text-sm font-medium  text-blue-500 hover:text-gray-800 rounded-lg focus:outline-none" type="submit">
                            Upload                               
                        </button>
                    </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
    </section>

<!-- small modal -->
<div class="modal fade" id="smallModal" tabindex="-1" role="dialog" aria-labelledby="smallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="smallBody">
                <div>
                    <!-- the result to be displayed apply here -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // display a modal (small modal)
    $(document).on('click', '#smallButton', function(event) {
        event.preventDefault();
        let href = $(this).attr('data-attr');
        $.ajax({
            url: href
            , beforeSend: function() {
                $('#loader').show();
            },
            // return the result
            success: function(result) {
                $('#smallModal').modal("show");
                $('#smallBody').html(result).show();
            }
            , complete: function() {
                $('#loader').hide();
            }
            , error: function(jqXHR, testStatus, error) {
                console.log(error);
                alert("Page " + href + " cannot open. Error:" + error);
                $('#loader').hide();
            }
            , timeout: 8000
        })
    });
</script>

</x-app-layout>