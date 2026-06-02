@props(['program' => null, 'name' => null, 'shortDescription' => null, 'title' => null])

@php
    $label = $program
        ? $program->listLabel()
        : \App\Models\Program::labelFrom($shortDescription, $name);
    $fullName = $program?->name ?? $name;
    $tooltip = $title ?? ($label !== $fullName ? $fullName : null);
@endphp

<span @if($tooltip) title="{{ $tooltip }}" @endif>{{ $label }}</span>
