import { createApp } from 'vue';
import { createPinia } from 'pinia';
import { adminRouter as router } from './router/timesheet-admin';
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

    const isSupervisor = el?.dataset?.isSupervisor || null;
    if (isSupervisor) {
        contextStore.setIsSupervisor(isSupervisor);
    }

    const isGlobalAdmin = el?.dataset?.isGlobalAdmin || null;
    if (isGlobalAdmin) {
        contextStore.setIsGlobalAdmin(isGlobalAdmin);
    }

    const hasPermissions = el?.dataset?.hasPermissions || null;
    if (hasPermissions) {
        contextStore.setHasPermissions(hasPermissions);
    }

    app.use(gettext);

    app.use(router);

    app.mount('#studip-timesheet-plugin-admin-app');
}
