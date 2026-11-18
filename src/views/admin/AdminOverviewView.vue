<template>
    <h2>Admin Overview</h2>
</template>
<script setup>
import { computed, onMounted, ref } from 'vue';

import { useContextStore } from '../../store/context';
import { useContractStore } from '../../store/contract';
import { usePermissionStore } from '../../store/permission';
import { useSupervisorStore } from '../../store/supervisor';

const contextStore = useContextStore();
const contractStore = useContractStore();
const permissionStore = usePermissionStore();
const supervisorStore = useSupervisorStore();

const selectedInstituteId = ref(null);

const userId = computed(() => {
    return contextStore.userId;
});

const currentUserPermissions = computed(() => {
    return permissionStore.byUserId(userId.value);
});


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

})


</script>