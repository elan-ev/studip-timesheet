<template>
    <div class="admin-layout">
        <header v-if="breadcrumbItems.length > 0" class="admin-layout__header">
            <SuiBreadcrumb
                :items="breadcrumbItems"
                link-type="router"
                ariaLabel="Navigation"
            />
        </header>

        <main class="admin-layout__content">
            <RouterView />
        </main>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useContextStore } from '@/store/context.js';
import { useSheetStore } from '@/store/sheet.js';
import SuiBreadcrumb from '@/components/ui/SuiBreadcrumb.vue';

const route = useRoute();
const router = useRouter();
const contextStore = useContextStore();
const sheetStore = useSheetStore();

const ROUTE_HIERARCHY = {
    'admin-overview': ['admin-overview'],
    'admin-permissions': ['admin-overview', 'admin-permissions'],
    'admin-contracts-overview': ['admin-overview', 'admin-contracts-overview'],
    'admin-contract-detail': ['admin-overview', 'admin-contracts-overview', 'admin-contract-detail'],
    'admin-sheet-detail': [
        'admin-overview',
        'admin-contracts-overview',
        'admin-contract-detail',
        'admin-sheet-detail',
    ],
};

const routeTitles = computed(() => {
    const map = new Map();
    for (const r of router.getRoutes()) {
        if (r.name && r.meta?.title) {
            map.set(r.name, r.meta.title);
        }
    }
    return map;
});

const activeContractId = computed(() => {
    if (route.name === 'admin-contract-detail') {
        return String(route.params.id ?? '');
    }
    if (route.name === 'admin-sheet-detail') {
        const sheet = sheetStore.byId?.(String(route.params.id ?? ''));
        const fromStore = sheet?.['contract-id'] ?? sheet?.attributes?.['contract-id'];
        return String(fromStore ?? route.query.contractId ?? '');
    }
    return '';
});

const breadcrumbItems = computed(() => {
    const currentName = String(route.name ?? '');
    const chain = ROUTE_HIERARCHY[currentName] ?? [];

    const visibleChain = contextStore.isGlobalAdmin
        ? chain
        : chain.filter((name) => name !== 'admin-overview');

    return visibleChain
        .map((name) => {
            const label = routeTitles.value.get(name);
            if (!label) return null;

            let to = '';
            if (name === 'admin-contract-detail') {
                if (!activeContractId.value) return null;
                to = router.resolve({
                    name: 'admin-contract-detail',
                    params: { id: activeContractId.value },
                }).path;
            } else if (name === 'admin-sheet-detail') {
                to = router.resolve({
                    name: 'admin-sheet-detail',
                    params: { id: route.params.id },
                }).path;
            } else {
                to = router.resolve({ name }).path;
            }

            return {
                id: name,
                label,
                to,
            };
        })
        .filter(Boolean);
});

</script>

<style lang="scss" scoped>
#studip-timesheet-plugin-admin-app {
    position: relative;
    height: 100%;
}

.admin-layout {
    display: flex;
    flex-direction: column;
    gap: 1rem;

    &__header {
        display: flex;
        align-items: center;
        min-height: 2rem;
    }

    &__content {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        min-width: 0;
    }
}
</style>