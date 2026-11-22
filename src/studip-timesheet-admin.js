import { createApp } from 'vue';
import { createPinia } from 'pinia';
import { adminRouter as router } from './router/timesheet-admin';
import { useContextStore } from './store/context';
import App from './StudipTimesheetAdminApp.vue';
import { gettext } from './i18n.js';
import PrimeVue from 'primevue/config'

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
    const userId = el?.dataset?.userId || null;
    if (userId) {
        contextStore.setUserId(userId);
    }

    app.use(gettext);

    app.use(router);

    app.use(PrimeVue, {
        locale: {
            firstDayOfWeek: 1,
            dayNames: ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'],
            dayNamesShort: ['So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa'],
            dayNamesMin: ['So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa'],
            monthNames: ['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'],
            monthNamesShort: ['Jan', 'Feb', 'Mär', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez'],
            today: 'Heute',
            clear: 'Leeren',
            weekHeader: 'KW'
        }
    });

    app.mount('#studip-timesheet-plugin-admin-app');
}
