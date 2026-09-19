<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <x-metas/>
        <title>
            @if (! empty($title))
                {{ $title }} — {{ config('app.name') }}
            @else
                {{ config('app.name') }}
            @endif
        </title>

        <script>
            (function () {
                var key = 'vrp-theme';
                var stored = localStorage.getItem(key);
                var theme = (stored === 'dark' || stored === 'light')
                    ? stored
                    : (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
                document.documentElement.dataset.theme = theme;
                document.documentElement.style.colorScheme = theme;
            })();
        </script>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body @class([
        'marketing-page',
        'marketing-page--narrow' => $narrow ?? false,
    ])>
        <a href="#main-content" class="marketing-skip">{{ __('messages.landing_skip_content') }}</a>

        <header class="marketing-header">
            <a href="{{ route('welcome') }}" class="marketing-brand">
                <span class="marketing-brand__name">{{ config('app.name') }}</span>
            </a>

            <nav class="marketing-nav" aria-label="{{ __('messages.landing_nav') }}">
                <button
                    type="button"
                    id="theme-toggle"
                    class="theme-toggle marketing-nav__theme"
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

                @unless (request()->routeIs('welcome'))
                    <a href="{{ route('welcome') }}" class="marketing-nav__link">{{ __('messages.landing_home') }}</a>
                @endunless

                @unless (request()->routeIs('login'))
                    <a href="{{ route('login') }}" class="marketing-nav__link">{{ __('messages.login') }}</a>
                @endunless

                @unless (request()->routeIs('account-request.*'))
                    <a href="{{ route('account-request.create') }}" class="btn btn-primary btn--compact marketing-nav__cta">
                        {{ __('messages.landing_request_access') }}
                    </a>
                @endunless
            </nav>
        </header>

        <main id="main-content" class="marketing-main" tabindex="-1">
            {{ $slot }}
        </main>

        <footer class="marketing-footer">
            <p class="marketing-footer__copy">
                &copy; {{ date('Y') }} XDM Consulting — Marc Augier
            </p>
            <p class="marketing-footer__tagline">{{ __('messages.landing_footer_tagline') }}</p>
        </footer>
    </body>
</html>
