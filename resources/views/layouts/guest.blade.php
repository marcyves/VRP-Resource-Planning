<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <x-metas/>
        <title>{{ config('app.name', 'Laravel') }}</title>

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
    <body>
        <h1 class="maintenance-title">{{ config('app.name') }}</h1>
        <img  class="maintenance-logo" src="/images/VRP.jpeg" alt="VRP" width="200">
            {{ $slot }}
        <footer>
        &copy; 2024 XDM Consulting - Marc Augier
        </footer>
    </body>
</html>
