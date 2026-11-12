<input type="checkbox" id="sidebar-toggle" class="sidebar-toggle-input" aria-hidden="true" tabindex="-1">

<label for="sidebar-toggle" class="sidebar-backdrop" aria-hidden="true"></label>

<aside class="app-sidebar" aria-label="{{ __('messages.nav_menu') }}">
    <div class="app-sidebar__brand">
        <a href="{{ Auth::user()->isSuperAdmin() ? route('super-admin.companies.index') : route('planning.index') }}" class="app-sidebar__brand-logo" aria-label="{{ config('app.name') }}">
            <span class="app-sidebar__logo">
                <x-application-logo />
            </span>
        </a>
        <div class="app-sidebar__brand-text">
            <a href="{{ Auth::user()->isSuperAdmin() ? route('super-admin.companies.index') : route('planning.index') }}" class="app-sidebar__app-name">{{ config('app.name') }}</a>
            @if (Auth::user()->isSuperAdmin())
                <span class="app-sidebar__app-tag">{{ __('messages.super_admin_platform') }}</span>
            @elseif (Auth::user()->company)
                <a href="{{ route('company.show') }}" class="app-sidebar__app-tag">{{ Auth::user()->company->name }}</a>
            @endif
        </div>
    </div>

    <nav class="app-sidebar__nav">
        @if (Auth::user()->isSuperAdmin())
            <x-sidebar-nav-link
                icon="grid"
                :href="route('super-admin.companies.index')"
                :active="request()->routeIs('super-admin.companies.*')"
            >
                {{ __('messages.super_admin_companies') }}
            </x-sidebar-nav-link>
            <x-sidebar-nav-link
                icon="receipt"
                :href="route('super-admin.electronic-invoicing.edit')"
                :active="request()->routeIs('super-admin.electronic-invoicing.*')"
            >
                {{ __('messages.super_admin_electronic_invoicing') }}
            </x-sidebar-nav-link>
            <x-sidebar-nav-link
                icon="shield"
                :href="route('super-admin.login-stats.index')"
                :active="request()->routeIs('super-admin.login-stats.*')"
            >
                {{ __('messages.login_stats') }}
            </x-sidebar-nav-link>
        @else
            @php
                $onBilling = request()->routeIs('billing.prepare', 'billing.prepare.*', 'school.billing.*')
                    || (request()->routeIs('school.show') && request('panel') === 'billing')
                    || request('focus') === 'billing';
                $onInvoices = request()->routeIs('treasury.invoices.*', 'invoice.*');
                $onExpenses = request()->routeIs('treasury.expenses.*', 'treasury.reports.*');
                $onSchools = ! $onBilling && request()->routeIs(
                    'home',
                    'dashboard',
                    'school.dashboard',
                    'school.index',
                    'school.list',
                    'school.show',
                    'school.create',
                    'school.edit',
                    'school.add',
                    'course.*'
                );
                $onBank = request()->routeIs('treasury.index', 'treasury.bank.*', 'treasury.reconciliation.*');
                $onPrograms = request()->routeIs('program.*');
                $onLogins = request()->routeIs('login-stats.*');
            @endphp

            <p class="sidebar-nav-section__label">{{ __('messages.nav_daily') }}</p>
            <x-sidebar-nav-link icon="calendar-range" :href="route('planning.index')" :active="request()->routeIs('planning.*', 'calendar.*')">
                {{ __('messages.planning') }}
            </x-sidebar-nav-link>
            <x-sidebar-nav-link icon="receipt" :href="route('billing.prepare')" :active="$onBilling">
                {{ __('messages.nav_invoice') }}
            </x-sidebar-nav-link>

            <p class="sidebar-nav-section__label">{{ __('messages.nav_activity') }}</p>
            <x-sidebar-nav-link icon="school" :href="route('home')" :active="$onSchools">
                {{ __('messages.schools') }}
            </x-sidebar-nav-link>
            <x-sidebar-nav-link icon="users" :href="route('group.index')" :active="request()->routeIs('group.*')">
                {{ __('messages.groups') }}
            </x-sidebar-nav-link>
            <x-sidebar-nav-link icon="layers" :href="route('program.index')" :active="$onPrograms">
                {{ __('messages.programs') }}
            </x-sidebar-nav-link>

            <p class="sidebar-nav-section__label">{{ __('messages.nav_finance') }}</p>
            <x-sidebar-nav-link icon="wallet" :href="route('treasury.invoices.index')" :active="$onInvoices">
                {{ __('messages.invoices') }}
            </x-sidebar-nav-link>
            <x-sidebar-nav-link icon="cart" :href="route('treasury.expenses.index')" :active="$onExpenses">
                {{ __('messages.expenses') }}
            </x-sidebar-nav-link>
            <x-sidebar-nav-link icon="banknote" :href="route('treasury.bank.index')" :active="$onBank">
                {{ __('messages.bank') }}
            </x-sidebar-nav-link>

            <p class="sidebar-nav-section__label">{{ __('messages.nav_more') }}</p>
            <x-sidebar-nav-link icon="building" :href="route('company.show')" :active="request()->routeIs('company.*')">
                {{ __('messages.my_company') }}
            </x-sidebar-nav-link>
            <x-sidebar-nav-link icon="person" :href="route('profile.edit')" :active="request()->routeIs('profile.edit', 'profile.update', 'profile.destroy')">
                {{ __('messages.profile') }}
            </x-sidebar-nav-link>
            @if (Auth::user()->isAdmin())
                <x-sidebar-nav-link icon="shield" :href="route('login-stats.index')" :active="$onLogins">
                    {{ __('messages.login_stats') }}
                </x-sidebar-nav-link>
            @endif
        @endif
    </nav>

    <div class="app-sidebar__footer">
        <button
            type="button"
            id="sidebar-compact-toggle"
            class="sidebar-link sidebar-link--button sidebar-compact-toggle"
            aria-pressed="false"
            data-label-expand="{{ __('messages.sidebar_expand') }}"
            data-label-collapse="{{ __('messages.sidebar_collapse') }}"
            aria-label="{{ __('messages.sidebar_collapse') }}"
            title="{{ __('messages.sidebar_collapse') }}"
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
