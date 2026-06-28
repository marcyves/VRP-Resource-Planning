@props([
    'name' => 'terminology_profile',
    'selected' => null,
])

@php
    $current = old($name, $selected ?? \App\Models\Company::PROFILE_EDUCATION);
@endphp

<select {{ $attributes->merge(['name' => $name, 'id' => $name, 'class' => 'form-input']) }}>
    @foreach (\App\Models\Company::terminologyProfileValues() as $profile)
        <option value="{{ $profile }}" @selected($current === $profile)>
            {{ __('messages.terminology_profile_' . $profile) }}
        </option>
    @endforeach
</select>
