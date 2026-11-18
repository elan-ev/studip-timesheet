<template>
    <form @submit.prevent="onSubmit" class="sui-form" novalidate @input="handleInteraction" @reset="onReset">

        <div class="sui-form-sr-alerts" aria-live="assertive" role="alert" aria-atomic="true">
            <template v-if="allErrorsForScreenreader.length > 0">
                <h2>Formular konnte nicht gespeichert werden.</h2>
                <p>Es gibt {{ allErrorsForScreenreader.length }} Fehler in diesem Formular:</p>
                <ol>
                    <li v-for="(msg, index) in allErrorsForScreenreader" :key="index">
                        {{ msg }}
                    </li>
                </ol>
            </template>
        </div>
        <div v-if="displayServerError" class="sui-form--error-container" role="none">
            <div class="sui-form--error-box">
                {{ displayServerError }}
            </div>
        </div>

        <div class="sui-form__content">
            <component v-for="(vnode, index) in validFormElements" :is="vnode" :key="index" />
        </div>

        <div v-if="validActionElements.length" class="sui-form__actions">
            <component v-for="(vnode, index) in validActionElements" :is="vnode" :key="index" />
        </div>
    </form>
</template>

<script setup>
import { ref, provide, nextTick, computed, watch, useSlots } from 'vue';
import { useSlotFilter } from '../../composables/useSlotFilter';

const emit = defineEmits(['submit', 'reset', 'update:error']);

defineExpose({
    validate: () => {
        onSubmit();
    },
    reset: () => {
        onReset();
    }
});

const props = defineProps({
    error: {
        type: String,
        default: ''
    },
    forceDisplayError: {
        type: Boolean,
        default: false
    }
});

const fields = ref(new Set());
const slots = useSlots();
const { getValidChildren } = useSlotFilter(slots);
const localErrorMessages = ref([]);
const internalServerError = ref('');

const validFormElements = computed(() => {
    let establishedMode = null;

    return getValidChildren((vnode) => {
        const compType = vnode.type;
        const componentName = compType?.name || compType?.__name || '';

        const isFieldset = componentName === 'SuiFieldset';

        const isFormField = compType?.isSuiFormField === true;

        if (!isFieldset && !isFormField) {
            if (compType !== Symbol.for('v-fgt') && import.meta.env.DEV) {
                console.warn(
                    `[UI Kit] SuiForm: Element <${componentName || 'Unbekannt'}> ist weder ein SuiFieldset noch ein registriertes Formularfeld und wurde entfernt.`
                );
            }
            return false;
        }

        if (establishedMode === null) {
            establishedMode = isFieldset ? 'FIELDSET' : 'FIELDS';
        }

        const matchesMode = (establishedMode === 'FIELDSET' && isFieldset) ||
            (establishedMode === 'FIELDS' && isFormField);

        if (!matchesMode && import.meta.env.DEV) {
            console.warn(
                `[UI Kit] SuiForm: Mischung aus SuiFieldset und nackten Formularfeldern ist nicht erlaubt. Element <${componentName}> wurde entfernt.`
            );
        }

        return matchesMode;
    });
});

const validActionElements = computed(() => {
    const actionNodes = slots.actions ? slots.actions() : [];
    return actionNodes.filter((vnode) => {
        const componentName =
            vnode.type?.name ||
            vnode.type?.__name ||
            '';

        const isButton = componentName === 'SuiButton';

        if (!isButton && import.meta.env.DEV) {
            console.warn(
                `[UI Kit] SuiForm: Im actions-Slot sind ausschließlich SuiButton erlaubt.`
            );
        }

        return isButton;
    });
});

const allErrorsForScreenreader = computed(() => {
    const combined = [...localErrorMessages.value];
    if (props.error) {
        combined.unshift(`Server-Fehler: ${props.error}`);
    }
    return combined;
});

const displayServerError = computed(() => internalServerError.value);

provide('SuiFormContext', {
    registerField: (field) => {
        fields.value.add(field);
    },
    unregisterField: (field) => {
        fields.value.delete(field);
    }
});

const onSubmit = async () => {
    localErrorMessages.value = [];
    internalServerError.value = '';
    await nextTick();

    let isAllValid = true;
    const newErrors = [];

    fields.value.forEach((field) => {
        const isValid = field.validate();
        if (!isValid) {
            isAllValid = false;
            newErrors.push(`${field.label}: ${field.getError()}`);
        }
    });

    if (!isAllValid) {
        localErrorMessages.value = newErrors;
        return;
    }

    emit('submit');
};

const clearServerError = () => {
    if (props.forceDisplayError) {
        return;
    }

    if (internalServerError.value) {
        internalServerError.value = '';
        emit('update:error', '');
    }
};

const handleInteraction = () => {
    clearServerError();
};

const onReset = () => {
    localErrorMessages.value = [];
    internalServerError.value = '';
    emit('update:error', '');

    fields.value.forEach((field) => {
        if (typeof field.reset === 'function') {
            field.reset();
        }
    });

    emit('reset');
};

watch(() => props.error, (newVal) => {
    internalServerError.value = newVal;
}, { immediate: true });
</script>

<style lang="scss">
.sui-form-sr-alerts {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    border: 0;
}

.sui-form {
    &--error-container {
        margin-bottom: 16px;
    }

    &--error-box {
        padding: 12px 16px;
        background-color: var(--sui-color-error-bg, #fff5f5);
        border-left: 4px solid var(--sui-color-error, #cc0000);
        color: var(--sui-color-error-text, #cc0000);
        font-weight: 500;
        font-size: 14px;
    }
}
</style>