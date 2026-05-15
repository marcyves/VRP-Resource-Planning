@props(['years', 'months', 'current_year', 'current_month', 'route'])

<div class="period-controls">
    <nav class="period-nav" aria-label="{{ __('messages.planning') }}">
        <a href="{{ route($route.'.previous') }}" class="period-nav__btn" aria-label="{{ __('pagination.previous') }}">‹</a>

        <form class="period-nav__form" action="{{ route($route.'.index') }}" method="get">
            <select
                id="period-month-{{ $route }}"
                name="current_month"
                class="period-nav__select"
                onchange="this.form.submit()"
                aria-label="{{ __('messages.planning') }}"
            >
                @foreach ($months as $index => $month)
                    <option value="{{ $index }}" @selected($index == $current_month - 1)>{{ $month }}</option>
                @endforeach
            </select>
        </form>

        <a href="{{ route($route.'.next') }}" class="period-nav__btn" aria-label="{{ __('pagination.next') }}">›</a>
    </nav>

    @if($route === 'billing')
        <a href="{{ route('billing.byDate') }}" class="period-nav__link">{{ __('messages.date_billing') }}</a>
    @endif
</div>
