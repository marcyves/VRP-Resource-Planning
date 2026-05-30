<input type="checkbox" id="sidebar-toggle" class="sidebar-toggle-input" aria-hidden="true" tabindex="-1">

<label for="sidebar-toggle" class="sidebar-backdrop" aria-hidden="true"></label>

<aside class="app-sidebar" aria-label="{{ __('messages.nav_menu') }}">
    <a href="{{ route('company.show') }}" class="app-sidebar__brand">
        <span class="app-sidebar__logo">
            <x-application-logo />
        </span>
        <span class="app-sidebar__brand-text">
            <span class="app-sidebar__app-name">{{ config('app.name') }}</span>
            <span class="app-sidebar__app-tag">{{ Auth::user()->company->name ?? '' }}</span>
        </span>
    </a>

    <nav class="app-sidebar__nav">
        <x-sidebar-nav-link
            icon="calendar-range"
            :href="route('planning.index')"
            :active="request()->routeIs('planning.*', 'calendar.*', 'billing.*')"
        >
            {{ __('messages.planning') }}
        </x-sidebar-nav-link>

        <x-sidebar-nav-link
            icon="receipt"
            :href="route('invoice.index')"
            :active="request()->routeIs('invoice.*')"
        >
            {{ __('messages.invoices') }}
        </x-sidebar-nav-link>

        <x-sidebar-nav-link
            icon="wallet"
            :href="route('treasury.index')"
            :active="request()->routeIs('treasury.*')"
        >
            {{ __('messages.treasury') }}
        </x-sidebar-nav-link>

        <x-sidebar-nav-link
            icon="grid"
            :href="route('dashboard')"
            :active="request()->routeIs('dashboard', 'school.dashboard', 'school.index', 'school.list', 'school.show', 'school.create', 'school.edit', 'school.add', 'program.*', 'course.*', 'group.*')"
        >
            {{ __('messages.workload_plan') }}
        </x-sidebar-nav-link>
    </nav>

    <div class="app-sidebar__footer">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="sidebar-link sidebar-link--button">
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
