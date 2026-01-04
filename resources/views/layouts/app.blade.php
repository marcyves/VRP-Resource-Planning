<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <x-metas />
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
        @if (isset($header))
        {{ $header }}
        @endif
        <x-alert type="success" class="alert alert-success" />
        <x-alert type="warning" class="alert alert-warning" />
        <x-alert type="danger" class="alert alert-danger" />
        @if($errors->any())
        <ul class="alert alert-danger list-unstyled">
            @foreach($errors->all() as $error)
            <li>- {{ $error }}</li>
            @endforeach
        </ul>
        @endif
    </header>

    <!-- Page Content -->
    <main>
        {{ $slot }}
    </main>
    @include('layouts.footer')
</body>

</html>