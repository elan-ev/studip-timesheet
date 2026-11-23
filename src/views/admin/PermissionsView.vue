<template>
    <section class="permissions-view">
        <section class="permissions-view__section">
            <h3 class="permissions-view__heading">
                {{ gettext.$gettext('Rollen & Berechtigungen') }}
            </h3>

            <p v-if="permissionStore.isLoading && permissions.length === 0" class="permissions-view__status">
                {{ gettext.$gettext('Berechtigungen werden geladen…') }}
            </p>

            <table v-else-if="permissions.length > 0" class="default">
                <thead>
                    <tr>
                        <th>{{ gettext.$gettext('Nutzername') }}</th>
                        <th>{{ gettext.$gettext('Einrichtung') }}</th>
                        <th>{{ gettext.$gettext('Rolle') }}</th>
                        <th class="actions">{{ gettext.$gettext('Aktionen') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in permissions" :key="item.id">
                        <td>
                            {{ getAttr(item, 'user-name') || `#${getAttr(item, 'user-id')}` }}
                        </td>
                        <td>
                            {{ getAttr(item, 'institute-name') || getAttr(item, 'institute-id') || '–' }}
                        </td>
                        <td>
                            {{ formatRole(getAttr(item, 'role')) }}
                        </td>
                        <td class="actions">
                            <PermissionUpdateDrawer :permission-id="item.id"/>
                        </td>
                    </tr>
                </tbody>
            </table>

            <p v-else class="permissions-view__status">
                {{ gettext.$gettext('Keine Berechtigungen vorhanden.') }}
            </p>
        </section>

        <section class="permissions-view__section">
            <h3 class="permissions-view__heading">
                {{ gettext.$gettext('Supervisors') }}
            </h3>

            <p v-if="supervisorStore.isLoading && supervisors.length === 0" class="permissions-view__status">
                {{ gettext.$gettext('Supervisors werden geladen…') }}
            </p>

            <table v-else-if="supervisors.length > 0" class="default">
                <thead>
                    <tr>
                        <th>{{ gettext.$gettext('Nutzername') }}</th>
                        <th>{{ gettext.$gettext('Vertrag') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="supervisor in supervisors" :key="supervisor.id">
                        <td>
                            {{ getAttr(supervisor, 'user-name') || `#${getAttr(supervisor, 'user-id')}` }}
                        </td>
                        <td>
                            <RouterLink
                                v-if="getAttr(supervisor, 'contract-id')"
                                :to="{
                                    name: 'admin-contract-detail',
                                    params: { id: String(getAttr(supervisor, 'contract-id')) },
                                }"
                            >
                                {{ gettext.$gettext('Vertrag') }} #{{ getAttr(supervisor, 'contract-id') }}
                            </RouterLink>
                            <span v-else>–</span>
                        </td>
                    </tr>
                </tbody>
            </table>

            <p v-else class="permissions-view__status">
                {{ gettext.$gettext('Keine Supervisors vorhanden.') }}
            </p>
        </section>
    </section>

    <PermissionCreateDrawer />
</template>

<script setup>
import { computed, onMounted } from 'vue';
import PermissionCreateDrawer from './permissions/PermissionCreateDrawer.vue';
import PermissionUpdateDrawer from './permissions/PermissionUpdateDrawer.vue';
import { gettext } from '@/i18n.js';
import { usePermissionStore } from '@/store/permission.js';
import { useSupervisorStore } from '@/store/supervisor.js';

const permissionStore = usePermissionStore();
const supervisorStore = useSupervisorStore();

const permissions = computed(() => {
    if (Array.isArray(permissionStore.all)) {
        return permissionStore.all;
    }
    void permissionStore.records?.size;
    return permissionStore.records ? Array.from(permissionStore.records.values()) : [];
});

const supervisors = computed(() => supervisorStore.all ?? []);

function getAttr(record, key) {
    return record?.attributes?.[key] ?? record?.[key];
}

function formatRole(role) {
    const labels = {
        superadmin: gettext.$gettext('Superadmin'),
        admin: gettext.$gettext('Admin'),
        supervisor: gettext.$gettext('Supervisor'),
    };
    return labels[role] ?? String(role ?? '–');
}

onMounted(async () => {
    await Promise.all([
        permissionStore.fetchAll(),
        supervisorStore.fetchAll(),
    ]);
});
</script>

<style lang="scss" scoped>
.permissions-view {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;

    &__title {
        margin: 0;
        font-size: 1.35rem;
    }

    &__section {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    &__heading {
        margin: 0;
        font-size: 1.1rem;
    }

    &__status {
        margin: 0;
        padding: 1rem;
        background: var(--color--content-bg-light, #fafbfd);
        color: var(--color--font-secondary, #666666);
    }
}
</style>