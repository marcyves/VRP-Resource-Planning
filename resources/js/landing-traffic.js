const allowedCtas = ['login', 'register', 'pricing'];

function trackingDeclined() {
    return navigator.doNotTrack === '1'
        || navigator.doNotTrack === 'yes'
        || navigator.globalPrivacyControl === true;
}

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

function ctaUrl() {
    return document.querySelector('meta[name="landing-cta-url"]')?.getAttribute('content') || '';
}

function sendCta(cta) {
    if (trackingDeclined() || !allowedCtas.includes(cta)) {
        return;
    }

    const url = ctaUrl();

    if (!url) {
        return;
    }

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ cta }),
        keepalive: true,
        credentials: 'same-origin',
    }).catch(() => {});
}

document.addEventListener('click', (event) => {
    const link = event.target.closest('[data-landing-cta]');

    if (!(link instanceof HTMLElement)) {
        return;
    }

    sendCta(link.getAttribute('data-landing-cta') || '');
});
