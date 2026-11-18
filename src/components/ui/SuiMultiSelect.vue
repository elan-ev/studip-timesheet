<template>
    <div class="sui-field-group" :class="{ 'sui-field-group--invalid': showError }">
        <label :for="uuid" class="sui-input-label">
            {{ label }}
            <span v-if="isRequired" class="sui-field-required" aria-hidden="true">*</span>
        </label>

        <div class="sui-input-wrapper">
            <MultiSelect v-bind="$attrs" :id="uuid" ref="selectRef" v-model="model" :unstyled="true" :show-clear="false"
                :pt="ptConfig" :aria-invalid="showError ? 'true' : 'false'"
                :aria-describedby="showError ? `${uuid}-error` : (hint ? `${uuid}-hint` : undefined)"
                @change="clearErrorOnChange">
                <template v-for="(_, name) in $slots" #[name]="slotData">
                    <slot :name="name" v-bind="slotData" />
                </template>

                <template #dropdownicon>
                    <SuiIcon shape="arr_1down" size="10" />
                </template>
                <template #itemcheckboxicon>
                    <SuiIcon shape="accept" size="10" />
                </template>
                <template #headercheckboxicon>
                    <SuiIcon shape="accept" size="10" />
                </template>
            </MultiSelect>
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
import MultiSelect from 'primevue/multiselect';
import SuiIcon from './SuiIcon.vue';

defineOptions({
    name: 'SuiMultiSelect',
    isSuiFormField: true,
    inheritAttrs: false
});

const props = defineProps({
    label: { type: String, required: true },
    required: { type: Boolean, default: false },
    hint: { type: String, default: '' },
});

const uuid = useId();
const selectRef = ref(null);

const model = defineModel({ default: () => [] });
const internalError = ref('');
const wasValidated = ref(false);

const showError = computed(() => wasValidated.value && !!internalError.value);
const isRequired = computed(() => props.required);

const formContext = inject('SuiFormContext', null);

const ptConfig = computed(() => ({
    root: ({ state, props: sProps }) => ({
        class: [
            'sui-multiselect',
            { 'sui-multiselect--open': state.overlayVisible },
            { 'sui-multiselect--error': showError.value },
            { 'sui-multiselect--disabled': sProps.disabled },
        ],
    }),
    labelContainer: { class: 'sui-multiselect-display-container' },
    label: { class: 'sui-multiselect-display' },
    dropdown: { class: 'sui-multiselect-dropdown' },
    overlay: { class: 'sui-multiselect-overlay' },
    list: { class: 'sui-multiselect-list' },
    option: ({ context }) => ({
        class: [
            'sui-multiselect-option',
            { 'sui-multiselect-option--selected': context.selected },
            { 'sui-multiselect-option--focused': context.focused },
            { 'sui-multiselect-option--disabled': context.disabled },
        ],
    }),
    header: { class: 'sui-multiselect-header' },
    emptyMessage: { class: 'sui-multiselect-empty' },

    pcFilterContainer: {
        root: { class: 'sui-multiselect-filter-wrapper' }
    },
    pcFilter: {
        root: { class: 'sui-multiselect-filter-field' }
    },
    pcFilterIconContainer: {
        root: { class: 'sui-multiselect-filter-icon-zone' }
    },

    pcHeaderCheckbox: {
        root: ({ context }) => ({
            class: [
                'sui-multiselect-checkbox',
                { 'sui-multiselect-checkbox--checked': context.checked }
            ]
        })
    },
    pcOptionCheckbox: {
        root: ({ context }) => ({
            class: [
                'sui-multiselect-checkbox',
                { 'sui-multiselect-checkbox--checked': context.checked }
            ]
        })
    },

    pcChip: {
        root: { class: 'sui-multiselect-chip' }
    }
}));

const validateField = () => {
    internalError.value = '';

    if (props.required) {
        const isEmpty = model.value === undefined || model.value === null || model.value === '' || (Array.isArray(model.value) && model.value.length === 0);
        if (isEmpty) {
            internalError.value = 'Wählen Sie mindestens eine Option aus.';
            return false;
        }
    }

    return true;
};

const clearErrorOnChange = () => {
    if (wasValidated.value) {
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
    focus: () => {
        selectRef.value?.$el?.querySelector('[role="combobox"]')?.focus() || selectRef.value?.$el?.focus();
    },
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

.sui-multiselect {
    display: inline-flex;
    height: 32px;
    background: #fff;
    border: 1px solid var(--color--content-box-border, #d0d2d3);
    border-radius: 4px;
    cursor: pointer;
    align-items: stretch;
    width: 100%;

    &:hover:not(.sui-multiselect--disabled) {
        border-color: #69767f;
    }

    &:focus-within,
    &.p-focus {
        border-color: #69767f;
        outline: none;
    }

    * {

        &:focus,
        &:focus-visible {
            outline: none !important;
        }
    }

    &--open {
        border-color: var(--color--highlight, #28497c);
    }

    &--disabled {
        background: var(--color--gray-6, #f1f2f3);
        cursor: not-allowed;
        opacity: 0.6;
    }

    &-display-container {
        display: flex;
        align-items: center;
        flex: 1;
        overflow: hidden;
    }

    &-display {
        padding: 0 12px;
        font-size: 14px;
        line-height: 30px;
        color: var(--color--font-primary, #333);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: flex;
        align-items: center;
        gap: 4px;
        flex-wrap: nowrap;

        &.p-placeholder {
            color: #7d7f80;
        }
    }

    &-dropdown {
        width: 2em;
        display: flex;
        justify-content: center;
        align-items: center;
        color: var(--color--highlight);
    }

    &-chip {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: var(--color--gray-5, #f8f9fa);
        border: 1px solid var(--color--gray-20, #e1e3e4);
        padding: 0 6px;
        height: 22px;
        border-radius: 3px;
        font-size: 12px;
        color: var(--color--font-primary, #333);
        white-space: nowrap;
    }
}

.sui-multiselect-overlay {
    background: #fff;
    border: 1px solid var(--color--highlight, #28497c);
    border-radius: 0 0 4px 4px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    z-index: 3000;
    margin-top: -3px;

    .sui-multiselect-header {
        padding: 8px;
        background: #fff;
        border-bottom: 1px solid var(--color--content-box-border, #d0d2d3);
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .sui-multiselect-filter-wrapper {
        position: relative;
        display: flex;
        align-items: center;
        flex: 1;

        .sui-multiselect-filter-field {
            width: 100%;
            padding: 8px 32px 8px 8px;
            border: 1px solid var(--color--content-box-border, #d0d2d3);
            border-radius: 4px;
            font-size: 14px;
            outline: none;
            background: #fff;
            color: var(--color--font-primary, #333);
            transition: border-color 0.15s ease-in-out;

            &:focus {
                border-color: var(--color--highlight, #28497c);
            }
        }

        .sui-multiselect-filter-icon-zone {
            position: absolute;
            right: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
            color: var(--color--font-secondary, #7d7f80);

            .p-icon {
                width: 14px;
                height: 14px;
            }
        }
    }

    .sui-multiselect-list {
        list-style: none;
        padding: 0;
        margin: 0;
        max-height: 250px;
        overflow-y: auto;
    }

    .sui-multiselect-option {
        padding: 8px;
        font-size: 14px;
        cursor: pointer;
        color: var(--color--font-primary, #333);
        display: flex;
        align-items: center;
        gap: 8px;
        transition: background-color 0.1s ease;
        position: relative;

        &--focused {
            background: #e7ebf1;
        }

        &--selected {
            background: #f4f6f9;
            font-weight: 600;
        }

        &--disabled {
            opacity: 0.5;
            cursor: not-allowed;
            background: none !important;
        }

        &:hover:not(.sui-multiselect-option--disabled) {
            background: #e7ebf1;
        }
    }

    .sui-multiselect-empty {
        padding: 8px 12px;
        font-size: 13px;
        color: #7d7f80;
        font-style: italic;
    }
}

.sui-multiselect-checkbox {
    position: relative;
    width: 14px;
    height: 14px;
    border: 1px solid var(--color--content-box-border, #d0d2d3);
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    color: transparent;
    transition: background-color 0.1s, border-color 0.1s;
    flex-shrink: 0;

    &--checked {
        border-color: var(--color--highlight, #28497c);
        color: var(--color--highlight, #28497c);
    }

    [data-pc-section="box"] {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
    }

    input[type="checkbox"] {
        position: absolute;
        cursor: pointer;
        appearance: none;
        padding: 0;
        margin: 0;
        opacity: 0;
        z-index: 1;
        outline: none;
        border: none;

        top: -12px;
        left: -12px;
        width: calc(100% + 24px);
        height: calc(100% + 24px);
    }
}
</style>
