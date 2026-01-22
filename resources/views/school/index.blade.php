<x-app-layout>
    @push('styles')
    @vite(['resources/css/schools.css', 'resources/css/pie.css'])
    @endpush

    <x-slot name="header">
        <div class="header-actions">
            <h2>{{ __('messages.schools_list') }}</h2>
            @if(Auth::user()->getMode() == "Edit")
            <a class="btn-school-list" href="{{route('school.list')}}">
                {{ __('messages.school_no_course') }}
            </a>
            @endif
        </div>
    </x-slot>

    <section class="glass-background">
        <ul class="school-grid">
            @php
            $total_amount = 0;
            $amounts = [];
            @endphp
            @foreach ($schools as $school)
            @php
            $total_amount += $school->amount;
            $amounts[] = $school->amount;
            @endphp
            <li class="school-card glass-background">
                <x-school-header :school_name="$school->name" :school_id="$school->id" />
                <div class="school-stats">
                    @money($school->amount) €
                </div>
            </li>
            @endforeach
        </ul>
    </section>

    <section class="glass-background">
        <div class="total-line">
            <span>Total invoices:</span>
            <span>@money($total_amount)€</span>
        </div>
    </section>

    @if($total_amount > 0)
    <section class="glass-background">
        <style>
            .pie {
                background-image: conic-gradient(from 30deg,
                        @php $current_percent =0;
                        @endphp @foreach($amounts as $amount) @php $percent =($amount / $total_amount) * 100;
                        $next_percent =$current_percent + $percent;

                        @endphp var(--c {
                                {
                                $loop->index
                            }

                        }) {
                            {
                            $current_percent
                        }
                    }

                    % {
                            {
                            $next_percent
                        }
                    }

                    % {
                            {
                            !$loop->last ? ',' : ''
                        }
                    }

                    @php $current_percent =$next_percent;
                    @endphp @endforeach );
            }
        </style>
        <figure class="charts">
            <div class="pie"></div>
            <figcaption>{{ __('messages.invoices_by_school') }}</figcaption>
        </figure>
    </section>
    @endif

    @if(Auth::user()->getMode() == "Edit")
    <section class="glass-background">
        <form action="{{route('school.store')}}" method="post" class="school-create-form glass-background-solid">
            @csrf
            <div class="school-form-input">
                <x-text-input type="text" name="name" id="name" placeholder="{{ __('messages.name') }}" value="{{old('name')}}" />
            </div>
            <div class="school-form-input">
                <x-text-input type="text" name="code" id="code" placeholder="{{ __('messages.code') }}" value="{{old('code')}}" />
            </div>
            <div class="school-form-input">
                <x-text-input type="text" name="address" id="address" placeholder="{{ __('messages.address') }}" value="{{old('address')}}" />
            </div>
            <div class="school-form-input">
                <x-text-input type="text" name="city" id="city" placeholder="{{ __('messages.city') }}" value="{{old('city')}}" />
            </div>
            <div class="school-form-input">
                <x-text-input type="text" name="zip" id="zip" placeholder="{{ __('messages.zip') }}" />
            </div>
            <div class="school-form-input">
                <x-text-input type="text" name="country" id="country" placeholder="{{ __('messages.country') }}" value="{{old('country', 'France')}}" />
            </div>
            <x-button-primary>{{ __('messages.school_create') }}</x-button-primary>
        </form>
    </section>
    @endif
</x-app-layout>