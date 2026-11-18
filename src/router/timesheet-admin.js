import { createWebHistory, createRouter } from 'vue-router';
import { gettext } from '../i18n.js';

const AdminOverviewView = () => import('../views/admin/AdminOverviewView.vue');
const ContractDetailView = () => import('../views/admin/ContractDetailView.vue');
const SheetDetailView = () => import('../views/admin/SheetDetailView.vue');
const NotFoundView = () => import('../views/NotFoundView.vue');

const routes = [
    {
        path: '/',
        name: 'admin-overview',
        component: AdminOverviewView,
        meta: { title: gettext.$gettext('Vertragsübersicht & Verwaltung') },
    },
    {
        path: '/contract/:id',
        name: 'admin-contract-detail',
        component: ContractDetailView,
        props: true,
        meta: { title: gettext.$gettext('Vertragsdetails & Stundennachweise') },
    },
        {
        path: '/sheet/:id',
        name: 'admin-sheet-detail',
        component: SheetDetailView,
        props: true,
        meta: { title: gettext.$gettext('Stundennachweis') },
    },
    {
        path: '/:pathMatch(.*)*',
        name: 'admin-not-found',
        component: NotFoundView,
        meta: { title: gettext.$gettext('Nicht gefunden') },
    },
];

const absoluteUriStudip = new URL(window.STUDIP.ABSOLUTE_URI_STUDIP);
const baseUrl = `${absoluteUriStudip.pathname}plugins.php/studiptimesheet/timesheetadmin/#`;

export const adminRouter = createRouter({
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

adminRouter.afterEach((to) => {
    if (to.meta.title) {
        currentTargetTitle = `${to.meta.title}${titleSeparator}${systemName}`;
        isSelfUpdating = true;
        document.title = currentTargetTitle;
        isSelfUpdating = false;
    }
});