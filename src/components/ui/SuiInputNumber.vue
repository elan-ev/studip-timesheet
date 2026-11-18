<template>
    <div class="sui-field-group" :class="{ 'sui-field-group--invalid': internalError }">
        <label v-if="label" :for="uuid" class="sui-input-label">
            {{ label }}
            <span v-if="isRequired" class="sui-field-required" aria-hidden="true">*</span>
        </label>

        <div class="sui-input-wrapper">
            <PrimeInputNumber :input-id="uuid" v-model="model" :min="min" :max="max" :step="step" :mode="mode"
                :prefix="prefix" :suffix="suffix" :currency="currency" :locale="locale" :format="format"
                :min-fraction-digits="minFractionDigits" :show-buttons="showButtons" :button-layout="sanitizedButtonLayout"
                :placeholder="placeholder" :invalid="!!internalError" v-bind="$attrs" :unstyled="true" :pt="ptConfig"
                :increment-button-class="'sui-input-number-button sui-input-number-increment-button'"
                :decrement-button-class="'sui-input-number-button sui-input-number-decrement-button'"
                @update:modelValue="clearErrorOnInput"
                >

                <template #incrementicon>
                    <SuiIcon shape="add" class="sui-input-number-icon" />
                </template>
                <template #decrementicon>
                    <SuiIcon shape="remove" class="sui-input-number-icon" />
                </template>
            </PrimeInputNumber>
        </div>

        <span v-if="showError" :id="`${uuid}-error`" class="sui-input-error-message">
            {{ internalError }}
        </span>
        <span v-if="!showError && hint !== ''" :id="`${uuid}-error`" class="sui-input-hint">
            {{ hint }}
        </span>
    </div>
</template>

<script setup>
import { ref, computed, useId, onMounted, onBeforeUnmount , inject, nextTick } from 'vue';
import * as v from 'valibot';
import PrimeInputNumber from 'primevue/inputnumber';
import SuiIcon from './SuiIcon.vue';

defineOptions({
    name: 'SuiInputNumber',
    isSuiFormField: true,
    inheritAttrs: false
});

const props = defineProps({
    label: { type: String, default: '' },
    placeholder: { type: String, default: '' },
    required: { type: Boolean, default: false },
    hint: { type: String, default: '' },
    rules: { type: [Object, Array], default: () => [] },
    min: { type: Number, default: null },
    max: { type: Number, default: null },
    step: { type: Number, default: 1 },
    mode: { type: String, default: 'decimal' },
    prefix: { type: String },
    suffix: { type: String },
    currency: { type: String },
    locale: { type: String, default: 'de-DE' },
    format: { type: Boolean, default: true },
    minFractionDigits: { type: Number, default: null },
    showButtons: { type: Boolean, default: false },
    buttonLayout: { type: String, default: 'stacked', validator: (value) => ['stacked', 'horizontal'].includes(value) },
});

const model = defineModel({ type: [Number, null], default: null });
const internalError = ref('');
const wasValidated = ref(false);
const uuid = useId();
const formContext = inject('SuiFormContext', null);

const showError = computed(() => wasValidated.value && !!internalError.value);
const isRequired = computed(() => props.required);

const sanitizedButtonLayout = computed(() => {
    return ['stacked', 'horizontal'].includes(props.buttonLayout)
        ? props.buttonLayout
        : 'stacked';
});

const ptConfig = computed(() => ({
    root: {
        class: [
            'sui-input-number-root',
            `sui-input-number-root--${props.buttonLayout}`
        ].join(' ')
    },
    pcInputText: {
        root: { class: 'sui-input' },
    },
    buttonGroup: {
        class: 'sui-input-number-button-group'
    },
}));

const combinedRules = computed(() => {
    const userRules = Array.isArray(props.rules) ? props.rules : [props.rules];
    const generatedRules = [];

    if (props.min !== null && props.min !== undefined) {
        generatedRules.push(v.minValue(props.min, `Der Wert darf nicht kleiner als ${props.min} sein.`));
    }
    if (props.max !== null && props.max !== undefined) {
        generatedRules.push(v.maxValue(props.max, `Der Wert darf nicht größer als ${props.max} sein.`));
    }

    return [...generatedRules, ...userRules].filter(Boolean);
});

const validateField = () => {
    reset();

    const isEmpty = model.value === undefined || model.value === null || String(model.value).trim() === '';

    if (isEmpty) {
        if (props.required) {
            internalError.value = 'Dieses Feld darf nicht leer sein.';
            return false;
        }
        return true;
    }

    try {
        for (const rule of combinedRules.value) {
            v.parse(v.pipe(v.number('Bitte gib eine gültige Zahl ein.'), rule), model.value);
        }
        return true;
    } catch (error) {
        if (error instanceof v.ValiError) {
            internalError.value = error.issues[0].message;
        }
        return false;
    }
};

const clearErrorOnInput = () => {
     nextTick(() => {
    if (wasValidated.value) {
        if (validateField()) {
            reset();
        }
    } });
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
    reset
};

onMounted(() => {
    formContext?.registerField(formInterface);
});
onBeforeUnmount(() => {
    formContext?.unregisterField(formInterface);
});
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
        color: var(--color--font-primary, #111111);

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
        font-size: 13px;
        color: var(--color--danger, #b91c1c);
        margin-top: 2px;
    }

    &.sui-field-group--invalid {
        .sui-input {
            border-color: var(--color--danger, #b91c1c);
            background-color: var(--color--danger-surface, #fef2f2);
        }

        // Korrektur für das horizontale Layout
        .sui-input-number-root--horizontal {
            .sui-input-number-decrement-button {
                border-color: var(--color--danger, #b91c1c);
                border-right-color: transparent;
            }
            .sui-input-number-increment-button {
                border-color: var(--color--danger, #b91c1c);
                border-left-color: transparent;
            }
        }

        .sui-input-number-root--stacked {
            .sui-input-number-button-group {
                border-color: var(--color--danger, #b91c1c);
            }
            .sui-input-number-increment-button {
                border-bottom: 1px solid var(--color--danger, #b91c1c);
            }
        }
    }
}

.sui-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    width: 100%;

    .sui-input-number-root {
        position: relative;
        display: flex;
        width: 100%;
    }

    .sui-input-number-root--horizontal {
        display: flex;
        align-items: stretch;
        width: 100%;

        .sui-input-number-decrement-button {
            order: 1;
            border: 1px solid var(--color--content-box-border, #d0d2d3);
            border-right: none;
            border-top-left-radius: 4px;
            border-bottom-left-radius: 4px;
            max-width: 40px;
            min-width: 40px;
            padding: 0;
        }

        .sui-input {
            order: 2;
            flex: 1;
            border-radius: 0;
            padding-right: 12px;

            &:focus {
                position: relative;
                z-index: 1;
            }
        }

        .sui-input-number-increment-button {
            order: 3;
            border: 1px solid var(--color--content-box-border, #d0d2d3);
            border-left: none;
            border-top-right-radius: 4px;
            border-bottom-right-radius: 4px;
            max-width: 40px;
            min-width: 40px;
            padding: 0;
        }
    }

    .sui-input-number-root--stacked {
        .sui-input {
            padding-right: 40px;
        }

        .sui-input-number-button-group {
            display: flex;
        }
    }

    .sui-input-number-root--horizontal {
        .sui-input-number-button-group {
            display: none;
        }
    }

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

    .sui-input-number-button-group {
        position: absolute;
        right: 1px;
        top: 1px;
        bottom: 1px;
        display: flex;
        flex-direction: column;
        width: 28px;
        border-left: 1px solid var(--color--content-box-border, #d0d2d3);
        border-top-right-radius: 3px;
        border-bottom-right-radius: 3px;
        overflow: hidden;
    }

    .sui-input-number-button {
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 1;
        background: var(--color--content-box-background, #ffffff);
        border: none;
        padding: 0;
        cursor: pointer;
        color: var(--color--font-secondary, #555555);
        transition: background-color 0.15s ease, color 0.15s ease;

        &:hover {
            background: var(--color--surface-hover, #f5f5f5);
            color: var(--color--highlight, #28497c);
        }

        &:focus {
            outline: none;
        }

        &.sui-input-number-increment-button {
            border-bottom: 1px solid var(--color--content-box-border, #d0d2d3);
        }
    }

    .sui-input-number-icon {
        width: 14px;
        height: 14px;
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
