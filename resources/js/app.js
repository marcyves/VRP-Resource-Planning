import './bootstrap';
import './theme';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.store('planningDelete', {
    url: '',
    label: '',
    date: '',
    request(url, label, date) {
        this.url = url;
        this.label = label;
        this.date = date;
        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'confirm-planning-delete' }));
    },
});

Alpine.start();
