import { createApp } from 'vue';
import { createPinia } from 'pinia';
import { createAdminRouter } from './router/timesheet-admin';
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

    function parseBoolean(value, fallback = false) {
        if (typeof value === 'boolean') return value;
        if (value === null || value === undefined) return fallback;
        if (typeof value === 'number') return value !== 0;
        if (typeof value === 'string') {
            const normalized = value.trim().toLowerCase();
            if (['true', '1', 'yes', 'on'].includes(normalized)) return true;
            if (['false', '0', 'no', 'off', ''].includes(normalized)) return false;
        }
        return fallback;
    }

    if (el?.dataset?.isSupervisor !== undefined) {
        contextStore.setIsSupervisor(parseBoolean(el.dataset.isSupervisor));
    }

    if (el?.dataset?.isGlobalAdmin !== undefined) {
        contextStore.setIsGlobalAdmin(parseBoolean(el.dataset.isGlobalAdmin));
    }

    if (el?.dataset?.hasPermissions !== undefined) {
        contextStore.setHasPermissions(parseBoolean(el.dataset.hasPermissions));
    }

    const userId = el?.dataset?.userId || null;
    if (userId) {
        contextStore.setUserId(userId);
    }

    const instituteSearch = el?.dataset?.instituteSearch || null;
    if (instituteSearch) {
        contextStore.setInstituteSearch(instituteSearch);
    }

    const userSearch = el?.dataset?.userSearch || null;
    if (userSearch) {
        contextStore.setUserSearch(userSearch);
    }

    const router = createAdminRouter();

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
