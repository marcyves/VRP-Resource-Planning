<header class="app-topbar">
    <div class="app-topbar__main">
        <label for="sidebar-toggle" class="app-topbar__menu-btn" aria-label="{{ __('messages.nav_menu') }}">
            <span class="app-topbar__menu-icon" aria-hidden="true">
                <x-rune-icon name="menu" :size="18" />
            </span>
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
                <div class="toggle-system app-topbar__mode">
                    <span class="toggle-label">{{ Auth::user()->getMode() }}</span>
                    <label class="toggle-container edit" for="edit-toggle">
                        <input class="toggle-checkbox" type="checkbox" id="edit-toggle" {{ Auth::user()->getMode() == 'Edit' ? 'checked' : '' }}>
                        <span class="toggle-track">
                            <span class="toggle-thumb"></span>
                        </span>
                    </label>
                </div>
            @endunless

            <a class="app-topbar__profile" href="{{ route('profile.edit') }}" title="{{ __('messages.profile') }}">
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
