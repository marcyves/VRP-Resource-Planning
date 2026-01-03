@props(['values'])
<div class="metrics glass-background">
    <h2>{{$values['title']}}</h2>
    <div class="metrics-row">
        <div class="histogram">
            @foreach ($values['amounts'] as $amount)
            @if ($amount > 0)
            @php
            $height = ($amount * 200) / $values['total'];
            @endphp
            <div class="histogram-bar" style="height: {{ $height }}%;">
                <span class="histogram-label">@moneyVAT($amount,0)</span>
            </div>
            @endif
            @endforeach
        </div>
    </div>
</div>