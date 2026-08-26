import { createWebHistory, createRouter } from 'vue-router';
import { gettext } from '../i18n.js';
const OverviewView = () => import('../views/OverviewView.vue');
const SheetView = () => import('../views/SheetView.vue');
const NotFoundView = () => import('../views/NotFoundView.vue');

const routes = [
    {
        path: '/',
        name: 'overview',
        component: OverviewView,
        meta: { title: gettext.$gettext('Übersicht der Zeiterfassung') },
    },
    {
        path: '/sheet/:id?',
        name: 'sheet',
        component: SheetView,
        props: true,
        meta: {
            title: gettext.$gettext('Stundennachweise der Zeiterfassung'),
        },
    },
    {
        path: '/:pathMatch(.*)*',
        name: 'not-found',
        component: NotFoundView,
        meta: { title: gettext.$gettext('Nicht gefunden') },
    },
];
const absoluteUriStudip = new URL(window.STUDIP.ABSOLUTE_URI_STUDIP);
const baseUrl = `${absoluteUriStudip.pathname}plugins.php/studiptimesheet/timesheet/#`;

export const router = createRouter({
    history: createWebHistory(baseUrl),
    routes,
    scrollBehavior() {
        return { top: 0 };
    },
});

const initialTitle = document.title;
let currentTargetTitle = '';
const titleSeparator = ' - ';
const systemName = initialTitle.includes(titleSeparator)
    ? initialTitle.substring(initialTitle.indexOf(titleSeparator) + titleSeparator.length)
    : 'Stud.IP';
let isSelfUpdating = false;
const titleEl = document.querySelector('title');
if (titleEl) {
    const observer = new MutationObserver(() => {
        if (isSelfUpdating) return;

        if (currentTargetTitle && document.title !== currentTargetTitle) {
            isSelfUpdating = true;
            document.title = currentTargetTitle;
            isSelfUpdating = false;
        }
    });
    observer.observe(titleEl, { childList: true, characterData: true, subtree: true });
}
router.afterEach((to) => {
    if (to.meta.title) {
        currentTargetTitle = `${to.meta.title} - ${systemName}`;
        isSelfUpdating = true;
        document.title = currentTargetTitle;
        isSelfUpdating = false;
    }
});
