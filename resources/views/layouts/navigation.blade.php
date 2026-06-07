<input type="checkbox" id="sidebar-toggle" class="sidebar-toggle-input" aria-hidden="true" tabindex="-1">

<label for="sidebar-toggle" class="sidebar-backdrop" aria-hidden="true"></label>

<aside class="app-sidebar" aria-label="{{ __('messages.nav_menu') }}">
    <div class="app-sidebar__brand">
        <a href="{{ route('home') }}" class="app-sidebar__brand-logo" aria-label="{{ config('app.name') }}">
            <span class="app-sidebar__logo">
                <x-application-logo />
            </span>
        </a>
        <div class="app-sidebar__brand-text">
            <a href="{{ route('home') }}" class="app-sidebar__app-name">{{ config('app.name') }}</a>
            @if (Auth::user()->company)
                <a href="{{ route('company.show') }}" class="app-sidebar__app-tag">{{ Auth::user()->company->name }}</a>
            @endif
        </div>
    </div>

    <nav class="app-sidebar__nav">
        <x-sidebar-nav-link
            icon="calendar-range"
            :href="route('planning.index')"
            :active="request()->routeIs('planning.*', 'calendar.*')"
        >
            {{ __('messages.planning') }}
        </x-sidebar-nav-link>

        <x-sidebar-nav-link
            icon="wallet"
            :href="route('treasury.index')"
            :active="request()->routeIs('treasury.*', 'invoice.*')"
        >
            {{ __('messages.treasury') }}
        </x-sidebar-nav-link>

        <x-sidebar-nav-link
            icon="grid"
            :href="route('home')"
            :active="request()->routeIs('home', 'dashboard', 'school.dashboard', 'school.index', 'school.list', 'school.show', 'school.create', 'school.edit', 'school.add', 'program.*', 'course.*', 'group.*')"
        >
            {{ __('messages.workload_plan') }}
        </x-sidebar-nav-link>
    </nav>

    <div class="app-sidebar__footer">
        <button
            type="button"
            id="sidebar-compact-toggle"
            class="sidebar-link sidebar-link--button sidebar-compact-toggle"
            aria-pressed="true"
            data-label-expand="{{ __('messages.sidebar_expand') }}"
            data-label-collapse="{{ __('messages.sidebar_collapse') }}"
            aria-label="{{ __('messages.sidebar_expand') }}"
            title="{{ __('messages.sidebar_expand') }}"
        >
            <span class="sidebar-link__icon sidebar-compact-toggle__icon sidebar-compact-toggle__icon--expand" aria-hidden="true">
                <x-module-tab-icon name="panel-left-open" />
            </span>
            <span class="sidebar-link__icon sidebar-compact-toggle__icon sidebar-compact-toggle__icon--collapse" aria-hidden="true">
                <x-module-tab-icon name="panel-left-close" />
            </span>
            <span class="sidebar-link__label sidebar-compact-toggle__label sidebar-compact-toggle__label--expand">{{ __('messages.sidebar_expand') }}</span>
            <span class="sidebar-link__label sidebar-compact-toggle__label sidebar-compact-toggle__label--collapse">{{ __('messages.sidebar_collapse') }}</span>
        </button>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button
                type="submit"
                class="sidebar-link sidebar-link--button"
                title="{{ __('messages.logout') }}"
                aria-label="{{ __('messages.logout') }}"
            >
                <span class="sidebar-link__icon" aria-hidden="true">
                    <x-module-tab-icon name="logout" />
                </span>
                <span class="sidebar-link__label">{{ __('messages.logout') }}</span>
            </button>
        </form>
    </div>
</aside>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('edit-toggle')?.addEventListener('change', (e) => {
            e.target.classList.add('toggled-once');
            window.location.href = "{{ route('profile.switch') }}";
        });

        document.querySelectorAll('.app-sidebar__nav .sidebar-link')?.forEach((link) => {
            link.addEventListener('click', () => {
                const toggle = document.getElementById('sidebar-toggle');
                if (toggle) {
                    toggle.checked = false;
                }
            });
        });
    });
</script>
