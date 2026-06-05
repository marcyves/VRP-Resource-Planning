import './bootstrap';
import './theme';
import './sidebar';

import Alpine from 'alpinejs';
import { createDeleteStore } from './delete-store';

window.Alpine = Alpine;

Alpine.store('planningDelete', createDeleteStore('confirm-planning-delete', ['label', 'date']));
Alpine.store('programDelete', createDeleteStore('confirm-program-delete', ['name']));
Alpine.store('groupDelete', createDeleteStore('confirm-group-delete', ['name']));
Alpine.store('documentDelete', createDeleteStore('confirm-document-delete', ['description']));

Alpine.start();
