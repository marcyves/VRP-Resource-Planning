<header class="app-topbar">
    <div class="app-topbar__main">
        <label for="sidebar-toggle" class="app-topbar__menu-btn" aria-label="{{ __('messages.nav_menu') }}">
            <span class="app-topbar__menu-icon" aria-hidden="true"></span>
        </label>

        @isset($pageHeader)
            <div class="app-topbar__title">
                {!! $pageHeader !!}
            </div>
        @endisset

        <div class="app-topbar__tools nav-context">
            <button
                type="button"
                id="theme-toggle"
                class="theme-toggle"
                aria-pressed="false"
                aria-label="{{ __('messages.theme_toggle') }}"
                title="{{ __('messages.theme_toggle') }}"
            >
                <span class="theme-toggle__icon theme-toggle__icon--sun" aria-hidden="true">
                    <x-module-tab-icon name="sun" />
                </span>
                <span class="theme-toggle__icon theme-toggle__icon--moon" aria-hidden="true">
                    <x-module-tab-icon name="moon" />
                </span>
            </button>

            @unless (Auth::user()->isSuperAdmin())
            <div class="toggle-system">
                <span class="toggle-label">{{ Auth::user()->getMode() }}</span>
                <label class="toggle-container edit" for="edit-toggle">
                    <input class="toggle-checkbox" type="checkbox" id="edit-toggle" {{ Auth::user()->getMode() == 'Edit' ? 'checked' : '' }}>
                    <span class="toggle-track">
                        <span class="toggle-thumb"></span>
                    </span>
                </label>
            </div>

            @php
                $current_year = session('current_year');
            @endphp

            <form class="nav-form" action="{{ route('date.select') }}" method="post">
                @csrf
                <select id="current_year" name="current_year" onchange="this.form.submit()">
                    <option value="all" {{ $current_year == 'all' ? 'selected' : '' }}>{{ __('actions.select_all') }}</option>
                    @isset($years)
                        @foreach ($years as $year)
                            <option value="{{ $year->year }}" {{ $current_year == $year->year ? 'selected' : '' }}>{{ $year->year }}</option>
                        @endforeach
                    @endisset
                </select>
            </form>
            @endunless

            <a href="{{ route('profile.edit') }}" class="app-topbar__profile" title="{{ Auth::user()->name }}">
                <span class="app-topbar__profile-icon" aria-hidden="true">
                    <x-module-tab-icon name="person" />
                </span>
                <span class="app-topbar__profile-name">{{ Auth::user()->name }}</span>
            </a>
        </div>
    </div>

    @unless (Auth::user()->isSuperAdmin())
        @include('layouts.breadcrumbs')
    @endunless
</header>
