import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/navigation.css',
                'resources/css/layout.css',
                'resources/css/buttons.css',
                'resources/css/forms.css',
                'resources/css/modals.css',
                'resources/css/cards.css',
                'resources/css/alerts.css',
                'resources/css/schools.css',
                'resources/css/groups.css',
                'resources/css/plannings.css',
                'resources/css/bills.css',
                'resources/css/courses.css',
                'resources/css/programs.css',
                'resources/css/profiles.css',
                'resources/css/calendar-manage.css',
                'resources/css/calendar-mapping.css',
            ],
            refresh: true,
        }),
    ],
});
