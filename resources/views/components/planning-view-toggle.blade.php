@props(['view' => 'month'])

<nav class="planning-view-toggle" aria-label="{{ __('messages.planning_view') }}">
    <a
        href="{{ route('planning.index', ['view' => 'month']) }}"
        class="planning-view-toggle__link {{ $view === 'month' ? 'is-active' : '' }}"
        aria-current="{{ $view === 'month' ? 'page' : 'false' }}"
    >
        {{ __('messages.month') }}
    </a>
    <a
        href="{{ route('planning.index', ['view' => 'week']) }}"
        class="planning-view-toggle__link {{ $view === 'week' ? 'is-active' : '' }}"
        aria-current="{{ $view === 'week' ? 'page' : 'false' }}"
    >
        {{ __('messages.week') }}
    </a>
</nav>
