import { createApp } from 'vue';
import { createPinia } from 'pinia';
import { router } from './router/timesheet';
import { useContextStore } from './store/context';
import App from './StudipTimesheetApp.vue';
import { gettext } from './i18n.js';

const el = document.getElementById('studip-timesheet-plugin-app');

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

    const isWorker = el?.dataset?.isWorker || null;
    if (isWorker) {
        contextStore.setIsWorker(isWorker);
    }

    const userId = el?.dataset?.userId || null;
    if (userId) {
        contextStore.setUserId(userId);
    }

    app.use(gettext);

    app.use(router);

    app.mount('#studip-timesheet-plugin-app');
}
