import { createApp } from 'vue';
import { createPinia } from 'pinia';
import { router } from './router/timesheet';
import { useContextStore } from './store/context';
import App from './StudipTimesheetApp.vue';
import { gettext } from './i18n.js';
import PrimeVue from 'primevue/config'

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

    app.use(PrimeVue, {
        zIndex: {
            modal: 1203,    // dialog, drawer
            overlay: 1203, // select, dropdown, datepicker (set higher than SuiDrawer)
            menu: 1203,    // overlay menus
            tooltip: 1203  // tooltip
        },
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

    app.mount('#studip-timesheet-plugin-app');
}
