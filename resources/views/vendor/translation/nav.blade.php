<nav class="header">

    <h1 class="text-lg px-6">{{ config('app.name') }}</h1>

    <ul class="flex-grow justify-end pr-2">
        <li>
            <a href="{{ route('dashboard') }}" class="{{ set_active('') }}{{ set_active('/') }}">
                @include('translation::icons.home')
                {{ __('Retour VRP') }}
            </a>
        </li>
        <li>
            <a href="{{ route('filament.admin.pages.dashboard') }}" class="{{ set_active('') }}{{ set_active('/admin') }}">
                @include('translation::icons.admin')
                {{ __('Admin Panel') }}
            </a>
        </li>
        <li>
            <a href="{{ route('languages.index') }}" class="{{ set_active('') }}{{ set_active('/create') }}">
                @include('translation::icons.globe')
                {{ __('translation::translation.languages') }}
            </a>
        </li>
        <li>
            <a href="{{ route('languages.translations.index', config('app.locale')) }}" class="{{ set_active('*/translations') }}">
                @include('translation::icons.translate')
                {{ __('translation::translation.translations') }}
            </a>
        </li>
    </ul>

</nav>