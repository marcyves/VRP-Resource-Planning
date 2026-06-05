const STORAGE_KEY = 'vrp-sidebar-compact';

export function isSidebarCompact() {
    const stored = localStorage.getItem(STORAGE_KEY);
    return stored !== 'false';
}

export function applySidebarCompact(compact) {
    document.documentElement.dataset.sidebarCompact = compact ? 'true' : 'false';

    const toggle = document.getElementById('sidebar-compact-toggle');
    if (toggle) {
        toggle.setAttribute('aria-pressed', compact ? 'true' : 'false');
        toggle.setAttribute(
            'aria-label',
            compact ? toggle.dataset.labelExpand : toggle.dataset.labelCollapse
        );
        toggle.setAttribute(
            'title',
            compact ? toggle.dataset.labelExpand : toggle.dataset.labelCollapse
        );
    }
}

export function initSidebar() {
    applySidebarCompact(isSidebarCompact());

    document.getElementById('sidebar-compact-toggle')?.addEventListener('click', () => {
        const next = document.documentElement.dataset.sidebarCompact !== 'true';
        localStorage.setItem(STORAGE_KEY, next ? 'true' : 'false');
        applySidebarCompact(next);
    });
}

initSidebar();
