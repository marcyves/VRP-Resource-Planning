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

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @vite(['resources/css/calendar.css'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    @if (\Session::has('success'))
                    <div class="text-green-400 border border-green-400 rounded-md p-4 my-8 mx-0">
                        <ul>
                            <li class="mb-2">{!! \Session::get('success') !!}</li>
                        </ul>
                    </div>
                    @endif
                    @if (\Session::has('error'))
                    <div class="text-red-400 border border-red-400 rounded-md p-4 my-8 mx-0">
                        <ul>
                            <li class="mb-2">{!! \Session::get('error') !!}</li>
                        </ul>
                    </div>
                    @endif        
                </div>    
                </header>
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
