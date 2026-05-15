@props(['title', 'color' => 'default'])
<div class="section-header mb-4">
    <h2 class="section-title">{{$title}}</h2>
    <div class="section-actions">
        {{ $slot }}
    </div>
</div>