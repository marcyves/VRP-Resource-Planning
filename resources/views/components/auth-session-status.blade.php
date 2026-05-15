@props(['status'])

@if ($status)
<p role="status">{{ $status }}</p>
@endif
