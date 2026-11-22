<!-- <template>
    <h2>Contracts Overview</h2>

    <StudipQuicksearch ref="instituteQuicksearchRef" :searchtype="instituteSearch" name="user-qs" @select="selectInstitute"
        :placeholder="$gettext('Institute Suchen')"></StudipQuicksearch>
</template>
<script setup>
import { computed, onMounted, ref } from 'vue';
import StudipQuicksearch from './../../components/ui/StudipQuicksearch.vue';

import { useContextStore } from '../../store/context';
import { useContractStore } from '../../store/contract';
import { usePermissionStore } from '../../store/permission';
import { useSupervisorStore } from '../../store/supervisor';

const contextStore = useContextStore();
const contractStore = useContractStore();
const permissionStore = usePermissionStore();
const supervisorStore = useSupervisorStore();

const selectedInstituteId = ref(null);

const userQuicksearchRef = ref(null);
const instituteQuicksearchRef = ref(null);

const userSearch = computed(() => contextStore.userSearch);
const instituteSearch = computed(() => contextStore.instituteSearch);

const userId = computed(() => {
    return contextStore.userId;
});

const currentUserPermissions = computed(() => {
    return permissionStore.byUserId(userId.value);
});

const selectUser = async (value) => {
    if (value instanceof Event || !value) {
        return;
    }
    console.log(value)
    userQuicksearchRef.value.clear();
}

const selectInstitute = async (value) => {
    if (value instanceof Event || !value) {
        return;
    }
    console.log(value)
    instituteQuicksearchRef.value.clear();
}


onMounted(async () => {
        // get Permission Objects for user
    await permissionStore.fetchByUserId(userId.value);


    // get all contracts if superadmin or root based on last selected institute
    if (contextStore.isGlobalAdmin) {
        // contractStore.fetchByInstituteId(selectedInstituteId)
    } else {
        // get Supervisor Objects for user
        await supervisorStore.byUserId(userId.value);

        // get contracts by institute user has ROLE_SUPERVISOR or ROLE_ADMIN
        const permissions = permissionStore.byUserId(userId.value);
        permissions.forEach(permission => {
            contractStore.byInstituteId(permission['institute-id']);
        });

        // get contracts from Supervisor Objects
        const supervisions = supervisorStore.byUserId(userId.value);
        supervisions.forEach(supervision => {
            contractStore.fetchById(supervision['contract-id']);
        });
    }
});


</script> -->

<template>
    <div>
        <h2>{{ $gettext('Vertragsübersicht') }}</h2>

        <div v-if="contextStore.isGlobalAdmin">
            <div v-if="selectedInstituteId && !isChangingInstitute">
                <span>
                    {{ $gettext('Ausgewähltes Institut:') }}
                    <strong>{{ selectedInstituteName || selectedInstituteId }}</strong>
                </span>
                <button type="button" @click="startChangingInstitute">
                    {{ $gettext('Institut ändern') }}
                </button>
            </div>

            <StudipQuicksearch v-else ref="instituteQuicksearchRef" :searchtype="instituteSearch" name="institute-qs"
                :placeholder="$gettext('Institute Suchen')" @select="selectInstitute" />
        </div>

        <div v-if="isLoading">
            {{ $gettext('Lade Daten...') }}
        </div>

        <div v-else-if="!contextStore.isGlobalAdmin || selectedInstituteId">
            <section v-if="permissionContracts.length > 0">
                <h3>{{ $gettext('Verträge über Berechtigungen / Institut') }}</h3>
                <table class="default">
                    <thead>
                        <tr>
                            <th>{{ $gettext('Vertrag') }}</th>
                            <th>{{ $gettext('Mitarbeiter') }}</th>
                            <th>{{ $gettext('Institut') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="contract in permissionContracts" :key="contract.id">
                            <td>
                                <router-link :to="{ name: 'admin-contract-detail', params: { id: contract.id } }">
                                    {{ contract.title || contract.name || contract.id }}
                                </router-link>
                            </td>
                            <td>{{ contract['employee-name'] }}</td>
                            <td>{{ contract['institute-id'] }}</td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <section v-if="supervisorContracts.length > 0">
                <h3>{{ $gettext('Betreute Verträge (Supervisor)') }}</h3>
                <table class="default">
                    <thead>
                        <tr>
                            <th>{{ $gettext('Vertrag') }}</th>
                            <th>{{ $gettext('Institut') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="contract in supervisorContracts" :key="contract.id">
                            <td>
                                <router-link :to="{ name: 'admin-contract-detail', params: { id: contract.id } }">
                                    {{ contract.title || contract.name || contract.id }}
                                </router-link>
                            </td>
                            <td>{{ contract['institute-id'] }}</td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <p v-if="permissionContracts.length === 0 && supervisorContracts.length === 0">
                {{ $gettext('Keine Verträge vorhanden.') }}
            </p>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useGettext } from 'vue3-gettext';
import StudipQuicksearch from './../../components/ui/StudipQuicksearch.vue';

import { useContextStore } from '../../store/context';
import { useContractStore } from '../../store/contract';
import { usePermissionStore } from '../../store/permission';
import { useSupervisorStore } from '../../store/supervisor';

const { $gettext } = useGettext();

const contextStore = useContextStore();
const contractStore = useContractStore();
const permissionStore = usePermissionStore();
const supervisorStore = useSupervisorStore();

const selectedInstituteId = ref(null);
const selectedInstituteName = ref('');
const isChangingInstitute = ref(false);
const instituteQuicksearchRef = ref(null);

const instituteSearch = computed(() => contextStore.instituteSearch);
const userId = computed(() => contextStore.userId);

const isLoading = computed(() => {
    return contractStore.isLoading || permissionStore.isLoading || supervisorStore.isLoading;
});

const permissionContracts = computed(() => {
    if (contextStore.isGlobalAdmin) {
        return selectedInstituteId.value ? contractStore.byInstituteId(selectedInstituteId.value) : [];
    }

    const permissions = permissionStore.byUserId(userId.value) || [];
    const instituteIds = [...new Set(permissions.map((p) => p['institute-id']).filter(Boolean))];
    return instituteIds.flatMap((instId) => contractStore.byInstituteId(instId));
});

const supervisorContracts = computed(() => {
    if (contextStore.isGlobalAdmin) {
        return [];
    }

    const supervisions = supervisorStore.byUserId(userId.value) || [];
    return supervisions
        .map((s) => contractStore.byId(s['contract-id']))
        .filter(Boolean);
});

const startChangingInstitute = () => {
    isChangingInstitute.value = true;
};

const selectInstitute = async (value, displayname = '') => {
    if (value instanceof Event || !value) {
        return;
    }

    selectedInstituteId.value = value;
    selectedInstituteName.value = displayname || '';
    isChangingInstitute.value = false;

    await contractStore.fetchByInstituteId(value);
    instituteQuicksearchRef.value?.clear?.();
};

onMounted(async () => {
    if (!userId.value) return;

    await permissionStore.fetchByUserId(userId.value);

    if (contextStore.isGlobalAdmin) {
        if (selectedInstituteId.value) {
            await contractStore.fetchByInstituteId(selectedInstituteId.value);
        }
        return;
    }

    await supervisorStore.fetchAll();

    const permissions = permissionStore.byUserId(userId.value) || [];
    const instituteIds = [...new Set(permissions.map((p) => p['institute-id']).filter(Boolean))];
    const supervisions = supervisorStore.byUserId(userId.value) || [];

    await Promise.all([
        ...instituteIds.map((instId) => contractStore.fetchByInstituteId(instId)),
        ...supervisions.map((s) => contractStore.fetchById(s['contract-id'])),
    ]);
});
</script>