<template>
    <SuiDrawer
        ref="drawerRef"
        :title="gettext.$gettext('Berechtigung bearbeiten')"
        :width="440"
        @open="hydrateForm"
    >
        <template #anchor>
            <slot name="trigger">
                <button type="button" class="button">
                    {{ triggerLabel || gettext.$gettext('Bearbeiten') }}
                </button>
            </slot>
        </template>

        <form id="permission-update-form" class="permission-drawer__form" @submit.prevent="handleSubmit">
            <div class="permission-drawer__field">
                <span class="permission-drawer__label">
                    {{ gettext.$gettext('Aktuelle Zuordnung') }}
                </span>
                <p class="permission-drawer__meta">
                    {{ currentUserLabel }}
                    <template v-if="currentInstituteLabel">
                        – {{ currentInstituteLabel }}
                    </template>
                </p>
            </div>

            <div class="permission-drawer__field">
                <label class="permission-drawer__label" for="permission-update-role-select">
                    {{ gettext.$gettext('Rolle') }}
                </label>
                <select
                    id="permission-update-role-select"
                    v-model="role"
                    class="permission-drawer__select"
                    @change="handleRoleChange"
                >
                    <option value="admin">{{ gettext.$gettext('Admin') }}</option>
                    <option value="supervisor">{{ gettext.$gettext('Supervisor') }}</option>
                    <option value="superadmin">{{ gettext.$gettext('Superadmin') }}</option>
                </select>
            </div>

            <div v-if="requiresInstitute" class="permission-drawer__field">
                <label class="permission-drawer__label" for="permission-update-institute-qs">
                    {{ gettext.$gettext('Einrichtung') }}
                </label>
                <StudipQuicksearch
                    ref="instituteQuicksearchRef"
                    input-id="permission-update-institute-qs"
                    :searchtype="instituteSearch"
                    name="institute-qs"
                    :placeholder="currentInstituteLabel || gettext.$gettext('Institute suchen')"
                    @select="selectInstitute"
                />
            </div>

            <p v-if="validationError" class="permission-drawer__error">
                {{ validationError }}
            </p>
        </form>

        <template #footer="{ close }">
            <button
                type="button"
                class="button permission-drawer__delete-btn"
                :disabled="permissionStore.isLoading"
                @click="handleDelete"
            >
                {{ gettext.$gettext('Löschen') }}
            </button>
            <button
                type="button"
                class="button"
                :disabled="permissionStore.isLoading"
                @click="close"
            >
                {{ gettext.$gettext('Abbrechen') }}
            </button>
            <button
                type="submit"
                form="permission-update-form"
                class="button"
                :disabled="permissionStore.isLoading"
            >
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

const props = defineProps({
    permissionId: {
        type: [String, Number],
        required: true,
    },
    triggerLabel: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['updated', 'deleted']);

const contextStore = useContextStore();
const permissionStore = usePermissionStore();

const drawerRef = useTemplateRef('drawerRef');
const userQuicksearchRef = useTemplateRef('userQuicksearchRef');
const instituteQuicksearchRef = useTemplateRef('instituteQuicksearchRef');

const validationError = ref('');
const selectedUserId = ref('');
const role = ref('admin');
const selectedInstituteId = ref('');

const permission = computed(() => permissionStore.byId(String(props.permissionId)));
const source = computed(() => permission.value?.attributes ?? permission.value ?? {});

const currentUserLabel = computed(() => {
    const name = source.value['user-name'];
    const uid = source.value['user-id'];
    if (name && uid) return `${name} (#${uid})`;
    return name || (uid ? `#${uid}` : '');
});

const currentInstituteLabel = computed(() => {
    const instId = source.value['institute-id'];
    if (instId === 'global') return gettext.$gettext('Global');
    const name = source.value['institute-name'];
    if (name && instId) return `${name} (#${instId})`;
    return name || (instId ? `#${instId}` : '');
});

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

function hydrateForm() {
    validationError.value = '';
    resetQuicksearch(userQuicksearchRef);
    resetQuicksearch(instituteQuicksearchRef);

    const attrs = source.value;
    selectedUserId.value = String(attrs['user-id'] ?? '');
    role.value = String(attrs.role ?? 'admin');

    const instId = String(attrs['institute-id'] ?? '');
    selectedInstituteId.value = role.value === 'superadmin' || instId === 'global' ? '' : instId;
}

function handleRoleChange() {
    if (role.value === 'superadmin') {
        selectedInstituteId.value = '';
        resetQuicksearch(instituteQuicksearchRef);
    }
    validationError.value = '';
}

async function handleSubmit() {
    validationError.value = '';

    const targetId = String(props.permissionId);
    if (!targetId) {
        validationError.value = gettext.$gettext('Keine gültige Berechtigung ausgewählt.');
        return;
    }

    if (!selectedUserId.value) {
        validationError.value = gettext.$gettext('Bitte eine Nutzerin oder einen Nutzer auswählen.');
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
        const updated = await permissionStore.update(targetId, payload);
        emit('updated', updated);
        drawerRef.value?.close();
    } catch {
        validationError.value = gettext.$gettext('Die Berechtigung konnte nicht aktualisiert werden.');
    }
}

async function handleDelete() {
    validationError.value = '';

    const targetId = String(props.permissionId);
    if (!targetId) return;

    try {
        await permissionStore.remove(targetId, true);
        emit('deleted', targetId);
        drawerRef.value?.close();
    } catch {
        validationError.value = gettext.$gettext('Die Berechtigung konnte nicht gelöscht werden.');
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

    &__meta {
        margin: 0;
        font-size: 0.9rem;
        color: var(--color--font-secondary, #555555);
    }

    &__select {
        width: 100%;
        padding: 0.45rem 0.6rem;
        box-sizing: border-box;
    }

    &__delete-btn {
        margin-right: auto;
    }

    &__error {
        margin: 0;
        color: var(--color--danger, #b91c1c);
        font-size: 0.875rem;
    }
}
</style>