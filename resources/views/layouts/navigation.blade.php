<nav class="nav-main">
    <!-- Primary Navigation Menu -->
    <!-- Logo -->
    <a href="{{ route('dashboard') }}" class="logo nav-logo">
        <x-application-logo />
    </a>
    <!-- Navigation Links -->
    <div class="nav-links">
        <x-nav-link :href="route('planning.index')" :active="request()->routeIs('planning.index')">
            {{ __('messages.planning') }}
        </x-nav-link>
        <x-nav-link :href="route('calendar.index')" :active="request()->routeIs('calendar.index')">
            {{ __('messages.calendar') }}
        </x-nav-link>

        <x-nav-link :href="route('billing.index')" :active="request()->routeIs('billing.index')">
            {{ __('messages.billing') }}
        </x-nav-link>
        <x-nav-link :href="route('school.index')" :active="request()->routeIs('school.index')">
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
        <!-- Edit switch -->
        <div class="toggle-system">
            <div class="toggle-container edit toggled-once">
                @if(Auth::user()->getMode() == "Edit")
                <input class="toggle-checkbox toggled-once" type="checkbox" id="edit-toggle" checked>
                @else
                <input class="toggle-checkbox" type="checkbox" id="edit-toggle">
                @endif
                {{Auth::user()->getMode() }}
                <div class="toggle-track">
                    <div class="toggle-thumb"></div>
                </div>
            </div>
        </div>
        <script>
            const checkbox = document.getElementById('edit-toggle');

            const detectToggleOnce = (e) => {
                e.target.classList.add('toggled-once');
                let url = "{{ route('profile.switch') }}";
                document.location.href = url;
            };

            checkbox.addEventListener('click', detectToggleOnce, {
                once: true
            });
        </script>
        @php
        $current_year = session('current_year');
        $current_semester = session('current_semester');
        @endphp
        <!-- Period selector -->
        <form class="nav-form" action="{{route('date.select')}}" method="post">
            @csrf
            <select id="current_year" name="current_year" onchange="this.form.submit()">
                <option value="all" @if($current_year=="all" )selected @endif>{{ __('actions.select_all')}}</option>
                @isset($years)
                @foreach ($years as $year)
                <option value="{{$year->year}}" @if($current_year==$year->year)selected @endif>{{$year->year}}</option>
                @endforeach
                @endisset
            </select>
        </form>
        <!-- Settings Dropdown -->
        <div class="nav-user">
            <x-dropdown align="right" width="48">
                <x-slot name="trigger">
                    <button class="nav-user-btn">
                        <div>{{ Auth::user()->name }} ({{Auth::user()->getStatusName()}})</div>

                        <div class="ml-1">
                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </button>
                </x-slot>

                <x-slot name="content">
                    <x-dropdown-link :href="route('profile.edit')">
                        {{ __('Profile') }}
                    </x-dropdown-link>

                    <!-- Authentication -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <x-dropdown-link :href="route('logout')"
                            onclick="event.preventDefault();
                                                this.closest('form').submit();">
                            {{ __('Log Out') }}
                        </x-dropdown-link>
                    </form>
                </x-slot>
            </x-dropdown>
        </div>
    </div>
    </div>
</nav>