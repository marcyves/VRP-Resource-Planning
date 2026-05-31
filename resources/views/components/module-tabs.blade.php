@props(['tabs'])

@php
    $resolveIcon = function (array $tab): string {
        if (! empty($tab['icon'])) {
            return $tab['icon'];
        }

        $href = (string) ($tab['href'] ?? '');

        return match (true) {
            str_contains($href, 'dashboard') => 'grid',
            str_contains($href, 'school') || str_ends_with(rtrim($href, '/'), '/home') => 'school',
            str_contains($href, 'program') => 'layers',
            str_contains($href, 'group') => 'users',
            str_contains($href, 'planning') => 'calendar-range',
            str_contains($href, 'calendar') => 'calendar',
            str_contains($href, 'by-date') || str_contains($href, 'byDate') => 'clock',
            str_contains($href, 'billing') => 'receipt',
            str_contains($href, '#treasury-summary') => 'chart',
            str_contains($href, '#expense-reports') => 'folder',
            str_contains($href, '#standalone-expenses') => 'wallet',
            str_contains($href, 'expenses/create') => 'plus',
            str_contains($href, 'invoice/create') => 'plus',
            str_contains($href, 'invoice') => 'receipt',
            str_contains($href, 'company') => 'building',
            str_contains($href, 'profile') => 'person',
            default => 'dot',
        };
    };
@endphp

<nav class="module-tabs" aria-label="{{ __('messages.module_navigation') }}">
    @foreach($tabs as $tab)
        @if ($tab['disabled'] ?? false)
            <span
                class="module-tab module-tab--disabled {{ ($tab['active'] ?? false) ? 'active' : '' }}"
                aria-disabled="true"
                title="{{ $tab['disabled_title'] ?? __('messages.invoice_create_requires_school') }}"
            >
                <span class="module-tab__icon">
                    <x-module-tab-icon :name="$resolveIcon($tab)" />
                </span>
                <span class="module-tab__label">{{ $tab['label'] }}</span>
            </span>
        @else
            <a href="{{ $tab['href'] }}" class="module-tab {{ ($tab['active'] ?? false) ? 'active' : '' }}">
                <span class="module-tab__icon">
                    <x-module-tab-icon :name="$resolveIcon($tab)" />
                </span>
                <span class="module-tab__label">{{ $tab['label'] }}</span>
            </a>
        @endif
    @endforeach
</nav>
