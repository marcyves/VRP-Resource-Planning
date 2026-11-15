@if ($rows->isEmpty())
    <p class="landing-traffic-tops__empty">{{ __('messages.landing_traffic_none') }}</p>
@else
    <ol class="landing-traffic-tops__list">
        @foreach ($rows as $row)
            <li>
                <span>{{ $row->label }}</span>
                <span>{{ $row->aggregate }}</span>
            </li>
        @endforeach
    </ol>
@endif
