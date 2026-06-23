import './bootstrap';
import './theme';
import './sidebar';
import './planning-calendar';

import Alpine from 'alpinejs';
import { createDeleteStore } from './delete-store';
import { createDuplicateStore } from './duplicate-store';

window.Alpine = Alpine;

Alpine.store('programDelete', createDeleteStore('confirm-program-delete', ['name']));
Alpine.store('groupDelete', createDeleteStore('confirm-group-delete', ['name']));
Alpine.store('documentDelete', createDeleteStore('confirm-document-delete', ['description']));
Alpine.store('planningDuplicate', createDuplicateStore('confirm-planning-duplicate', ['label', 'date']));

Alpine.start();
