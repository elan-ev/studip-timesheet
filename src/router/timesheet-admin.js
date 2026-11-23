// import { createWebHistory, createRouter } from 'vue-router';
// import { gettext } from '../i18n.js';

// import { useContextStore } from './../store/context.js';

// const AdminOverviewView = () => import('../views/admin/AdminOverviewView.vue');
// const ContractDetailView = () => import('../views/admin/ContractDetailView.vue');
// const ContractsOverviewView =  () => import('../views/admin/ContractsOverviewView.vue');
// const PermissionsView = () => import('../views/admin/PermissionsView.vue');
// const SheetDetailView = () => import('../views/admin/SheetDetailView.vue');
// const NotFoundView = () => import('../views/NotFoundView.vue');

// const routes = [
//     {
//         path: '/',
//         name: 'admin-overview',
//         component: AdminOverviewView,
//         meta: { title: gettext.$gettext('Vertragsübersicht & Verwaltung') },
//     },
//     {
//         path: '/permissions',
//         name: 'admin-permissions',
//         component: PermissionsView,
//         props: true,
//         meta: { title: gettext.$gettext('Berechtigungen') },
//     },
//     {
//         path: '/contracts',
//         name: 'admin-contracts-overview',
//         component: ContractsOverviewView,
//         props: true,
//         meta: { title: gettext.$gettext('Verträge') },
//     },
//     {
//         path: '/contract/:id',
//         name: 'admin-contract-detail',
//         component: ContractDetailView,
//         props: true,
//         meta: { title: gettext.$gettext('Vertragsdetails & Stundennachweise') },
//     },
//         {
//         path: '/sheet/:id',
//         name: 'admin-sheet-detail',
//         component: SheetDetailView,
//         props: true,
//         meta: { title: gettext.$gettext('Stundennachweis') },
//     },
//     {
//         path: '/:pathMatch(.*)*',
//         name: 'admin-not-found',
//         component: NotFoundView,
//         meta: { title: gettext.$gettext('Nicht gefunden') },
//     },
// ];

// const absoluteUriStudip = new URL(window.STUDIP.ABSOLUTE_URI_STUDIP);
// const baseUrl = `${absoluteUriStudip.pathname}plugins.php/studiptimesheet/timesheetadmin/#`;

// export const adminRouter = createRouter({
//     history: createWebHistory(baseUrl),
//     routes,
//     scrollBehavior() {
//         return { top: 0 };
//     },
// });

// const initialTitle = document.title;
// let currentTargetTitle = '';
// const titleSeparator = ' - ';
// const systemName = initialTitle.includes(titleSeparator)
//     ? initialTitle.substring(initialTitle.indexOf(titleSeparator) + titleSeparator.length)
//     : 'Stud.IP';
// let isSelfUpdating = false;
// const titleEl = document.querySelector('title');

// if (titleEl) {
//     const observer = new MutationObserver(() => {
//         if (isSelfUpdating) return;
//         if (currentTargetTitle && document.title !== currentTargetTitle) {
//             isSelfUpdating = true;
//             document.title = currentTargetTitle;
//             isSelfUpdating = false;
//         }
//     });
//     observer.observe(titleEl, { childList: true, characterData: true, subtree: true });
// }

// adminRouter.beforeEach((to) => {
//     const contextStore = useContextStore();

//     if (to.name === 'admin-overview' && !contextStore.isGlobalAdmin) {
//         return { name: 'admin-contracts-overview' };
//     }

//     if (to.name === 'admin-permissions' && !contextStore.isGlobalAdmin) {
//         return { name: 'admin-contracts-overview' };
//     }
// });

// adminRouter.afterEach((to) => {
//     if (to.meta.title) {
//         currentTargetTitle = `${to.meta.title}${titleSeparator}${systemName}`;
//         isSelfUpdating = true;
//         document.title = currentTargetTitle;
//         isSelfUpdating = false;
//     }
// });

import { createWebHistory, createRouter } from 'vue-router';
import { gettext } from '../i18n.js';

import { useContextStore } from './../store/context.js';

const AdminOverviewView = () => import('../views/admin/AdminOverviewView.vue');
const ContractDetailView = () => import('../views/admin/ContractDetailView.vue');
const ContractsOverviewView = () => import('../views/admin/ContractsOverviewView.vue');
const PermissionsView = () => import('../views/admin/PermissionsView.vue');
const SheetDetailView = () => import('../views/admin/SheetDetailView.vue');
const NotFoundView = () => import('../views/NotFoundView.vue');

const routes = [
    {
        path: '/',
        name: 'admin-overview',
        component: AdminOverviewView,
        meta: { title: gettext.$gettext('Zeiterfassung') },
    },
    {
        path: '/permissions',
        name: 'admin-permissions',
        component: PermissionsView,
        props: true,
        meta: { title: gettext.$gettext('Berechtigungen') },
    },
    {
        path: '/contracts',
        name: 'admin-contracts-overview',
        component: ContractsOverviewView,
        props: true,
        meta: { title: gettext.$gettext('Vertragsübersicht') },
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

export function createAdminRouter() {
    const absoluteUriStudip = new URL(window.STUDIP.ABSOLUTE_URI_STUDIP);
    const baseUrl = `${absoluteUriStudip.pathname}plugins.php/studiptimesheet/timesheetadmin/#`;

    const router = createRouter({
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

    router.beforeEach((to) => {
        const contextStore = useContextStore();
        if (to.name === 'admin-overview' && !contextStore.isGlobalAdmin) {
            return { name: 'admin-contracts-overview' };
        }

        if (to.name === 'admin-permissions' && !contextStore.isGlobalAdmin) {
            return { name: 'admin-contracts-overview' };
        }
    });

    router.afterEach((to) => {
        if (to.meta.title) {
            currentTargetTitle = `${to.meta.title}${titleSeparator}${systemName}`;
            isSelfUpdating = true;
            document.title = currentTargetTitle;
            isSelfUpdating = false;
        }
    });

    return router;
}