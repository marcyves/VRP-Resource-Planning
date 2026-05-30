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

Alpine.store('programDelete', {
    url: '',
    name: '',
    request(url, name) {
        this.url = url;
        this.name = name;
        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'confirm-program-delete' }));
    },
});

Alpine.store('groupDelete', {
    url: '',
    name: '',
    request(url, name) {
        this.url = url;
        this.name = name;
        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'confirm-group-delete' }));
    },
});

Alpine.start();
