<div class="calCell calDay">
    <div class="planning-date">
        {{$day}}
    </div>
    @php
    $day_gain = 0;
    $day_hours = 0;
    @endphp
    @foreach ($planning as $event)
    @php
    $begin_date = explode(" ", $event->begin)[0];
    $begin_day = explode("-", $begin_date)[2];
    if ((int)$begin_day == $day){
    $day_gain += $event->session_length * $event->rate;
    $day_hours += $event->session_length;
    @endphp
    <div class="planning-entry">
        <div class="planning-text">
            <div class="justify-start">
                {{date_format(date_create($event->begin),'H:i')}}: {{$event->short_name}} ({{$event->group_short_name}})
            </div>
            <div class="justify-start">
                {{date_format(date_create($event->end),'H:i')}}: {{number_format($event->session_length * $event->rate,2)}} €
            </div>
            <div class="text-green-400">
                {{$event->invoice_id}}
            </div>
        </div>
        @if(Auth::user()->getMode() == "Edit")
        <div class="planning-tools">
            <a href="{{route('planning.edit',$event->id)}}">
                <x-button-edit />
            </a>
            <form action="{{route('planning.delete',$event->id)}}" method="post">
                @csrf
                @method('delete')
                <x-button-delete />
            </form>
        </div>
        @endif
    </div>
    @php
    }
    @endphp
    @endforeach
    <div class="spacer"></div>
    <div class="planning-total">
        {{$day_hours}} {{ __('messages.hours_initial') }} - {{number_format($day_gain,2)}} €
    </div>
</div>