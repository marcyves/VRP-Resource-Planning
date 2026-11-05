@props(['name', 'size' => null])

{!! \App\Support\RuneIcons::svg((string) $name, $size !== null ? (int) $size : null, (string) ($attributes['class'] ?? '')) !!}
