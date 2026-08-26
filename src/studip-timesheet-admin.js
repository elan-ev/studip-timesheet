import { createApp } from 'vue';
import { createPinia } from 'pinia';
import { router } from './router/timesheet';
import { useContextStore } from './store/context';
import App from './StudipTimesheetAdminApp.vue';
import { gettext } from './i18n.js';

const el = document.getElementById('studip-timesheet-plugin-admin-app');

if (el) {
    const app = createApp(App);

    const pinia = createPinia();
    app.use(pinia);

    const contextStore = useContextStore();
    const preferredLanguage = el?.dataset?.preferredLanguage || null;
    if (preferredLanguage) {
        contextStore.setPreferredLanguage(preferredLanguage);
    }

    app.use(gettext);

    app.use(router);

    app.mount('#studip-timesheet-plugin-admin-app');
}
