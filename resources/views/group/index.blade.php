<x-app-layout>
    <x-slot name="header">
        <h2>
            {{ __('messages.groups_list') }}
        </h2>
    </x-slot>

    <section class="glass-background">
        <ul class="list">
            @foreach ($groups as $group)
            <li class="card glass-background">
                <x-group-details :group=$group :occurences=$occurences :active=true />
            </li>
            @endforeach
        </ul>
    </section>

    @if($inactive->count() > 0 || request('search'))
    <div class="mx-6 mt-6 mb-2 flex items-center justify-between">
        <h3 class="text-lg font-bold">{{ __('messages.inactive_groups') }}</h3>
        <form action="{{ route('group.index') }}" method="GET" class="flex gap-2">
            <x-text-input type="text" name="search" placeholder="Search..." value="{{ request('search') }}" />
            <x-primary-button type="submit">Search</x-primary-button>
            @if(request('search'))
            <a href="{{ route('group.index') }}" class="px-4 py-2 bg-gray-200 rounded-md text-gray-700 hover:bg-gray-300 transition">Clear</a>
            @endif
        </form>
    </div>

    <section class="glass-background opacity-75">
        <ul class="flex-list">
            @foreach ($inactive as $group)
            <li class="card glass-background">
                <x-group-details :group=$group :occurences=$occurences :active=false />
            </li>
            @endforeach
        </ul>
        <div class="mt-4">
            {{ $inactive->links() }}
        </div>
    </section>
    @endif

    @if(Auth::user()->getMode() == "Edit")
    <section class="glass-background">
        <form action="{{route('group.save', 0)}}" method="post"
            class="mx-auto px-6 py-2 bg-white shadow-md mb-6 flex flex-col gap-4">
            @csrf
            <x-text-input type="text" name="name" id="name" placeholder="{{ __('messages.name') }}" size="200" />
            @error('name')
            <div class="bg-red-700 text-red-100 border-red-400 rounded-md px-4 py-2">{{ $message }}</div>
            @enderror
            <x-text-input type="text" name="short_name" id="short_name" placeholder="{{ __('messages.short_name') }}" />
            @error('short_name')
            <div class="bg-red-700 text-red-100 border-red-400 rounded-md px-4 py-2">{{ $message }}</div>
            @enderror
            <x-text-input type="text" name="size" id="size" placeholder="{{ __('messages.size') }}" />
            @error('size')
            <div class="bg-red-700 text-red-100 border-red-400 rounded-md px-4 py-2">{{ $message }}</div>
            @enderror
            <x-text-input type="text" name="year" id="year" placeholder="{{ __('messages.year') }}" />
            @error('year')
            <div class="bg-red-700 text-red-100 border-red-400 rounded-md px-4 py-2">{{ $message }}</div>
            @enderror
            <x-primary-button>{{ __('messages.group_create') }}</x-primary-button>
        </form>
    </section>
    @endif


</x-app-layout>