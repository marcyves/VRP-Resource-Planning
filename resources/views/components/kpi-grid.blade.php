@props(['items' => []])

<div {{ $attributes->merge(['class' => 'kpi-grid']) }}>
    @foreach ($items as $item)
        <article @class([
            'kpi-card',
            'kpi-card--accent' => ($item['variant'] ?? 'accent') === 'accent',
            'kpi-card--success' => ($item['variant'] ?? '') === 'success',
            'kpi-card--warning' => ($item['variant'] ?? '') === 'warning',
            'kpi-card--info' => ($item['variant'] ?? '') === 'info',
            'kpi-card--total' => ($item['variant'] ?? '') === 'total',
        ])>
            @if (! empty($item['icon']))
                <span class="kpi-card__icon" aria-hidden="true">
                    <x-module-tab-icon :name="$item['icon']" />
                </span>
            @endif
            <div class="kpi-card__body">
                <p class="kpi-card__label">{{ $item['label'] }}</p>
                <p class="kpi-card__value">{{ $item['value'] }}</p>
                @if (! empty($item['hint']))
                    <p class="kpi-card__hint">{{ $item['hint'] }}</p>
                @endif
            </div>
        </article>
    @endforeach
</div>
