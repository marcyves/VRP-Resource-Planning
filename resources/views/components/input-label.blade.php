@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-sm font-bold text-gray-700']) }}>
    {{ $value ?? $slot }}
</label>
