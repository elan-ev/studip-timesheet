<template>
    <div class="sui-field-group" :class="{ 'sui-field-group--invalid': showError }">
        <label :for="uuid" class="sui-input-label">
            {{ label }}
            <span v-if="isRequired" class="sui-field-required" aria-hidden="true">*</span>
        </label>

        <div class="sui-input-wrapper" :class="{ 'has-icon': icon, 'is-clearable': clearable && model }">
            <input :id="uuid" ref="inputRef" v-model="model" :type="type" class="sui-input" :placeholder="placeholder"
                :aria-invalid="showError ? 'true' : 'false'" :aria-describedby="showError ? `${uuid}-error` : undefined"
                @input="clearErrorOnInput" v-bind="$attrs" />

            <div class="sui-input-adornments">
                <SuiButton v-if="clearable && model" icon="decline" :icon-only="true" :icon-only-unstyled="true"
                    size="normal" class="sui-input-clear-btn" @click="clearInput" />
                <template v-if="icon">
                    <SuiButton v-if="clickableIcon" class="sui-input-clickable-btn" :icon="icon" size="normal"
                        :icon-only="true" :icon-only-unstyled="true" @click="$emit('click-icon')" />
                    <SuiIcon v-else :shape="icon" size="16" class="sui-input-icon"
                        :class="{ 'is-clickable': $attrs.onClickIcon }" />
                </template>
            </div>
        </div>

        <span v-if="showError" :id="`${uuid}-error`" class="sui-input-error-message">
            {{ internalError }}
        </span>
        <span v-if="!showError && hint !== ''" :id="`${uuid}-hint`" class="sui-input-hint">
            {{ hint }}
        </span>
    </div>
</template>

<script setup>
import { ref, computed, useId, inject, onMounted, onBeforeUnmount } from 'vue';
import * as v from 'valibot';
import SuiIcon from './SuiIcon.vue';
import SuiButton from './SuiButton.vue';

defineOptions({
    name: 'SuiInput',
    isSuiFormField: true
});

const props = defineProps({
    label: { type: String, required: true },
    type: { type: String, default: 'text' },
    placeholder: { type: String, default: '' },
    icon: { type: String, default: null },
    clearable: { type: Boolean, default: false },
    clickableIcon: { type: Boolean, default: false },
    rules: { type: [Object, Array], default: () => [] },
    required: { type: Boolean, default: false },
    baseSchema: {
        type: Function,
        default: () => v.string()
    },
    hint: { type: String, default: '' },
});

const uuid = useId();
const inputRef = ref(null);

const model = defineModel({ type: [String, Number], default: '' });
const internalError = ref('');
const wasValidated = ref(false);

const showError = computed(() => wasValidated.value && !!internalError.value);

const isRequired = computed(() => props.required);

const formContext = inject('SuiFormContext', null);

const validateField = () => {
    internalError.value = '';
    const isEmpty = model.value === undefined || model.value === null || model.value === '';
    if (isEmpty) {
        if (props.required) {
            internalError.value = 'Dieses Feld darf nicht leer sein.';
            return false;
        }
        return true;
    }

    if (!props.rules || (Array.isArray(props.rules) && props.rules.length === 0)) return true;

    const rulesArray = Array.isArray(props.rules) ? props.rules : [props.rules];

    const finalRules = props.required
        ? [v.nonEmpty('Dieses Feld darf nicht leer sein.'), ...rulesArray]
        : rulesArray;

    try {
        for (const rule of finalRules.filter(Boolean)) {
            v.parse(v.pipe(props.baseSchema(), rule), model.value);
        }
        return true;
    } catch (error) {
        if (error instanceof v.ValiError) {
            internalError.value = error.issues[0].message;
        }
        return false;
    }
};

const clearInput = () => {
    model.value = '';
    if (wasValidated.value) {
        reset();
    }
};

const clearErrorOnInput = () => {
    if (wasValidated.value && validateField()) {
        reset();
    }
};

const reset = () => {
    internalError.value = '';
};

const formInterface = {
    label: props.label,
    validate: () => {
        wasValidated.value = true;
        return validateField();
    },
    getError: () => internalError.value,
    focus: () => inputRef.value?.focus(),
    reset
};

onMounted(() => formContext?.registerField(formInterface));
onBeforeUnmount(() => formContext?.unregisterField(formInterface));
</script>

<style lang="scss">
.sui-field-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
    max-width: 43em;
    margin-bottom: 1em;

    .sui-input-label {
        font-weight: 600;
        font-size: 14px;
        color: var(--color--font-primary);

        .sui-field-required {
            color: var(--color--danger, #b91c1c);
            margin-left: 2px;
        }
    }

    .sui-input-hint {
        font-size: 12px;
        color: var(--color--font-secondary);
        margin-top: 2px;
    }

    .sui-input-error-message {
        font-size: 12px;
        color: var(--color--danger, #b91c1c);
        margin-top: 2px;
    }

    &.sui-field-group--invalid {
        .sui-input {
            border-color: var(--color--danger, #b91c1c);
            background-color: var(--color--danger-surface, #fef2f2);
        }
    }
}

.sui-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    width: 100%;

    .sui-input {
        width: 100%;
        padding: 8px 12px;
        padding-right: 32px;
        border: 1px solid var(--color--content-box-border, #d0d2d3);
        border-radius: 4px;

        &:focus {
            outline: none;
            border-color: var(--color--highlight, #28497c);
        }
    }

    .sui-input-adornments {
        position: absolute;
        right: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
        pointer-events: none;

        .sui-input-clickable-btn,
        .sui-input-clear-btn {
            pointer-events: auto;
            padding: 0;
        }

        .sui-input-clickable-btn {
            margin-right: 0;
        }

        .sui-input-icon {
            color: var(--color--font-secondary);

            &.is-clickable {
                color: var(--color--highlight, #28497c);
            }
        }
    }
}
</style>
