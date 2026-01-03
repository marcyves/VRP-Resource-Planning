@props(['values'])
<div class="metrics glass-background">
    <h2>{{$values['title']}}</h2>
    <div class="metrics-row">
        <div class="histogram">
            @foreach ($values['amounts'] as $amount)
            @if ($amount > 0)
                <div class="histogram-bar" style="height: {{($amount*200)/$values['total']}}%;">
                    <span class="histogram-label">@moneyVAT($amount,0)</span>
                </div>
            @endif
            @endforeach
        </div>
    </div>
</div>
