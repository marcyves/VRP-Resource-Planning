<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <x-metas />
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

            var sidebarKey = 'vrp-sidebar-compact';
            var sidebarStored = localStorage.getItem(sidebarKey);
            document.documentElement.dataset.sidebarCompact = sidebarStored === 'false' ? 'false' : 'true';
        })();
    </script>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    @vite([
    'resources/css/app.css',
    'resources/js/app.js'])
</head>

<body class="app-shell">
    @include('layouts.navigation')
    <div class="app-canvas">
        @include('layouts.topbar', ['pageHeader' => $header ?? null])

        <div class="app-alerts">
            <x-alert type="success" class="alert alert-success" />
            <x-alert type="warning" class="alert alert-warning" />
            <x-alert type="danger" class="alert alert-danger" />
            @if($errors->any())
                <ul role="alert" class="app-errors">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif
        </div>

        <main class="app-main">
            {{ $slot }}
        </main>

        @include('layouts.footer')
    </div>
</body>

</html>
