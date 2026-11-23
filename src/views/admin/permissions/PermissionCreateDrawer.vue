<template>
    <SuiDrawer ref="drawerRef" v-model="isOpen" :title="gettext.$gettext('Berechtigung anlegen')" :width="440"
        @open="resetForm">
        <template #anchor="{ toggle }">
            <slot name="trigger" :toggle="toggle">
                <button type="button" class="button">
                    {{ triggerLabel || gettext.$gettext('Berechtigung hinzufügen') }}
                </button>
            </slot>
        </template>

        <form id="permission-create-form" class="permission-drawer__form" @submit.prevent="handleSubmit">
            <div class="permission-drawer__field">
                <label class="permission-drawer__label" for="permission-user-qs">
                    {{ gettext.$gettext('Nutzerin / Nutzer') }}
                </label>
                <StudipQuicksearch ref="userQuicksearchRef" input-id="permission-user-qs" :searchtype="userSearch"
                    name="user-qs" :placeholder="gettext.$gettext('Nutzerinnen und Nutzer suchen')"
                    @select="selectUser" />
            </div>

            <div class="permission-drawer__field">
                <label class="permission-drawer__label" for="permission-role-select">
                    {{ gettext.$gettext('Rolle') }}
                </label>
                <select id="permission-role-select" v-model="role" class="permission-drawer__select"
                    @change="handleRoleChange">
                    <option value="admin">{{ gettext.$gettext('Admin') }}</option>
                    <option value="supervisor">{{ gettext.$gettext('Supervisor') }}</option>
                    <option value="superadmin">{{ gettext.$gettext('Superadmin') }}</option>
                </select>
            </div>

            <div v-if="requiresInstitute" class="permission-drawer__field">
                <label class="permission-drawer__label" for="permission-institute-qs">
                    {{ gettext.$gettext('Einrichtung') }}
                </label>
                <StudipQuicksearch ref="instituteQuicksearchRef" input-id="permission-institute-qs"
                    :searchtype="instituteSearch" name="institute-qs"
                    :placeholder="gettext.$gettext('Institute suchen')" @select="selectInstitute" />
            </div>

            <p v-if="validationError" class="permission-drawer__error">
                {{ validationError }}
            </p>
        </form>

        <template #footer="{ close }">
            <button type="button" class="button" @click="close">
                {{ gettext.$gettext('Abbrechen') }}
            </button>
            <button type="submit" form="permission-create-form" class="button" :disabled="permissionStore.isLoading">
                {{ gettext.$gettext('Speichern') }}
            </button>
        </template>
    </SuiDrawer>
</template>

<script setup>
import { computed, ref, useTemplateRef } from 'vue';
import { gettext } from '@/i18n.js';
import { useContextStore } from '@/store/context.js';
import { usePermissionStore } from '@/store/permission.js';
import SuiDrawer from '@/components/ui/SuiDrawer.vue';
import StudipQuicksearch from '@/components/ui/StudipQuicksearch.vue';

defineProps({
    triggerLabel: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['created']);

const contextStore = useContextStore();
const permissionStore = usePermissionStore();

const drawerRef = useTemplateRef('drawerRef');
const userQuicksearchRef = useTemplateRef('userQuicksearchRef');
const instituteQuicksearchRef = useTemplateRef('instituteQuicksearchRef');

const isOpen = ref(false);
const validationError = ref('');

const selectedUserId = ref('');
const role = ref('admin');
const selectedInstituteId = ref('');

const userSearch = computed(() => contextStore.userSearch);
const instituteSearch = computed(() => contextStore.instituteSearch);
const requiresInstitute = computed(() => role.value !== 'superadmin');

function extractSelectedId(payload) {
    if (!payload) return '';
    if (typeof payload === 'string' || typeof payload === 'number') {
        return String(payload);
    }
    return String(
        payload.id ??
        payload.value ??
        payload.item_id ??
        payload.user_id ??
        payload.institute_id ??
        ''
    );
}

function selectUser(selection) {
    selectedUserId.value = extractSelectedId(selection);
    validationError.value = '';
}

function selectInstitute(selection) {
    selectedInstituteId.value = extractSelectedId(selection);
    validationError.value = '';
}

function resetQuicksearch(qsRef) {
    const instance = qsRef?.value;
    if (!instance) return;
    if (typeof instance.clear === 'function') {
        instance.clear();
    } else if (typeof instance.reset === 'function') {
        instance.reset();
    }
}

function resetForm() {
    selectedUserId.value = '';
    role.value = 'admin';
    selectedInstituteId.value = '';
    validationError.value = '';
    resetQuicksearch(userQuicksearchRef);
    resetQuicksearch(instituteQuicksearchRef);
}

function handleRoleChange() {
    if (!requiresInstitute.value) {
        selectedInstituteId.value = '';
        resetQuicksearch(instituteQuicksearchRef);
    }
    validationError.value = '';
}

async function handleSubmit() {
    validationError.value = '';

    if (!selectedUserId.value) {
        validationError.value = gettext.$gettext('Bitte eine Nutzerin oder einen Nutzer auswählen.');
        return;
    }

    if (requiresInstitute.value && !selectedInstituteId.value) {
        validationError.value = gettext.$gettext('Bitte eine Einrichtung auswählen.');
        return;
    }

    const isSuperadmin = role.value === 'superadmin';

    if (!isSuperadmin && !selectedInstituteId.value) {
        validationError.value = gettext.$gettext('Bitte eine Einrichtung auswählen.');
        return;
    }

    const payload = {
        'user-id': selectedUserId.value,
        role: role.value,
        'institute-id': isSuperadmin ? 'global' : selectedInstituteId.value,
    };

    try {
        const created = await permissionStore.create(payload);
        emit('created', created);
        drawerRef.value?.close();
    } catch {
        validationError.value = gettext.$gettext('Die Berechtigung konnte nicht gespeichert werden.');
    }
}
</script>

<style lang="scss" scoped>
.permission-drawer {
    &__form {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    &__field {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
    }

    &__label {
        font-size: 0.875rem;
        font-weight: 600;
    }

    &__select {
        width: 100%;
        padding: 0.45rem 0.6rem;
        box-sizing: border-box;
    }

    &__error {
        margin: 0;
        color: var(--color--danger, #b91c1c);
        font-size: 0.875rem;
    }
}
</style>