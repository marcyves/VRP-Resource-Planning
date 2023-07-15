@props(['color'])
<div class="py-2">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-{{$color}} overflow-hidden shadow-sm sm:rounded-lg px-6 py-4">
        {{ $slot }}
        </div>
    </div>
</div>