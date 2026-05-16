<nav class="nav-main">
<!-- Primary Navigation Menu -->
    <!-- Logo -->
    <a href="{{ route('dashboard') }}" class="nav-logo">
        <x-application-logo />
    </a>
    <!-- Navigation Links -->
    <div class="nav-links">
        <x-nav-link :href="route('planning.index')" :active="request()->routeIs('planning.index')">
            {{ __('messages.planning') }}
        </x-nav-link>
        <x-nav-link :href="route('billing.index')" :active="request()->routeIs('billing.index')">
            {{ __('messages.billing') }}
        </x-nav-link>
        <x-nav-link :href="route('school.index')" :active="request()->routeIs('school.index', 'school.list')">
            {{ __('messages.schools') }}
        </x-nav-link>
        <x-nav-link :href="route('program.index')" :active="request()->routeIs('program.index')">
            {{ __('messages.programs') }}
        </x-nav-link>
        <x-nav-link :href="route('group.index')" :active="request()->routeIs('group.index')">
            {{ __('messages.groups') }}
        </x-nav-link>
        <x-nav-link :href="route('invoice.index')" :active="request()->routeIs('invoice.index')">
            {{ __('messages.invoices') }}
        </x-nav-link>

        <div class="toggle-system">
            <span class="toggle-label">{{ Auth::user()->getMode() }}</span>
            <label class="toggle-container edit" for="edit-toggle">
                <input class="toggle-checkbox" type="checkbox" id="edit-toggle" {{ Auth::user()->getMode() == "Edit" ? 'checked' : '' }}>
                <span class="toggle-track">
                    <span class="toggle-thumb"></span>
                </span>
            </label>
        </div>

        <script>
            document.getElementById('edit-toggle')?.addEventListener('change', (e) => {
                e.target.classList.add('toggled-once');
                window.location.href = "{{ route('profile.switch') }}";
            });
        </script>

        @php
        $current_year = session('current_year');
        @endphp

        <form class="nav-form" action="{{route('date.select')}}" method="post">
            @csrf
            <select id="current_year" name="current_year" onchange="this.form.submit()">
                <option value="all" {{ $current_year == "all" ? 'selected' : '' }}>{{ __('actions.select_all')}}</option>
                @isset($years)
                @foreach ($years as $year)
                <option value="{{$year->year}}" {{ $current_year == $year->year ? 'selected' : '' }}>{{$year->year}}</option>
                @endforeach
                @endisset
            </select>
        </form>

        <details class="nav-user">
            <summary class="nav-user-btn">
                <span>{{ Auth::user()->name }} ({{Auth::user()->getStatusName()}})</span>
                <span class="nav-user-icon">
                    <svg class="icon-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </span>
            </summary>

            <div class="nav-user-menu">
                <a class="nav-user-menu__link" href="{{ route('profile.edit') }}">
                    {{ __('messages.profile') }}
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="nav-user-menu__link nav-user-menu__button">
                        {{ __('messages.logout') }}
                    </button>
                </form>
            </div>
        </details>
    </div>
</nav>