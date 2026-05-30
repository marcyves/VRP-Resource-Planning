@props(['years', 'months', 'current_year', 'current_month', 'route', 'school' => null, 'by_date' => false])

<div class="period-controls">
    <nav class="period-nav" aria-label="{{ __('messages.planning') }}">
        @if ($school)
            <a href="{{ route('school.billing.previous', $school) }}" class="period-nav__btn" aria-label="{{ __('pagination.previous') }}">‹</a>
        @else
            <a href="{{ route($route.'.previous') }}" class="period-nav__btn" aria-label="{{ __('pagination.previous') }}">‹</a>
        @endif

        <form class="period-nav__form" action="{{ $school ? route('school.show', $school) : route($route.'.index') }}" method="get">
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

        @if ($school)
            <a href="{{ route('school.billing.next', $school) }}" class="period-nav__btn" aria-label="{{ __('pagination.next') }}">›</a>
        @else
            <a href="{{ route($route.'.next') }}" class="period-nav__btn" aria-label="{{ __('pagination.next') }}">›</a>
        @endif
    </nav>

    @if ($school)
        <a href="{{ route('school.billing.byDate', $school) }}" class="period-nav__link">
            {{ $by_date ? __('messages.course') : __('messages.by_date') }}
        </a>
    @endif
</div>
