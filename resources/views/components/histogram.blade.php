@props(['values'])
<div class="metrics glass-background">
    <h2>{{$values['title']}}</h2>
    <div class="metrics-row">
        <div class="histogram">
            @foreach ($values['amounts'] as $amount)
                <div class="histogram-bar" style="height: {{($amount*200)/$values['total']}}%;">
                    <span class="histogram-label">@moneyVAT($amount,0)</span>
                </div>
            @endforeach
        </div>
    </div>
</div>
