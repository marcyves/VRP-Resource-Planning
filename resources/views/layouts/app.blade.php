<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Resources -->
        @vite([
            'resources/css/app.css',
            'resources/js/app.js'])
        <!-- Scripts -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
        <script src='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js' type='text/javascript'></script>
        <script src="https://unpkg.com/@material-tailwind/html@latest/scripts/dialog.js"></script>
    </head>
    <body>
        @include('layouts.navigation')
        @include('layouts.breadcrumbs')
        <!-- Page Heading -->
            <header>
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    @if (isset($header))
                        {{ $header }}
                    @endif
                    <x-alert type="success" class="bg-green-700 text-green-100 border border-green-400 rounded-md p-4 my-8 mx-0" />
                    <x-alert type="warning" class="bg-yellow-700 text-yellow-100 border-yellow-400 rounded-md p-4 my-8 mx-0" />
                    <x-alert type="danger" class="bg-red-700 text-red-100 border-red-400 rounded-md p-4 my-8 mx-0" />
                </div>    
            </header>

        <!-- Page Content -->
        <main>
            {{ $slot }}
        </main>
        @include('layouts.footer')
    </body>
</html>
