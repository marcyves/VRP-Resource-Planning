@props(['programs', 'active' => true])

<ul class="resource-grid{{ $active ? '' : ' resource-grid--inactive' }}">
    @foreach ($programs as $program)
        <x-program-card :program="$program" />
    @endforeach
</ul>
