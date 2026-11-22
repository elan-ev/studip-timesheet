<template>
    <div class="sui-field-group" :class="{ 'sui-field-group--invalid': showError }">
        <label :for="uuid" class="sui-input-label">
            {{ label }}
            <span v-if="isRequired" class="sui-field-required" aria-hidden="true">*</span>
        </label>

        <div class="sui-input-wrapper">
            <Select v-bind="$attrs" :id="uuid" ref="selectRef" v-model="model" :unstyled="true" :show-clear="false"
                :pt="ptConfig" :aria-invalid="showError ? 'true' : 'false'"
                :aria-describedby="showError ? `${uuid}-error` : (hint ? `${uuid}-hint` : undefined)"
                @change="clearErrorOnChange">
                <template v-for="(_, name) in $slots" #[name]="slotData">
                    <slot :name="name" v-bind="slotData" />
                </template>

                <template #dropdownicon>
                    <SuiIcon shape="arr_1down" size="10" />
                </template>
            </Select>
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
import Select from 'primevue/select';
import SuiIcon from './SuiIcon.vue';

defineOptions({
    name: 'SuiSelect',
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

const model = defineModel({ default: null });
const internalError = ref('');
const wasValidated = ref(false);

const showError = computed(() => wasValidated.value && !!internalError.value);
const isRequired = computed(() => props.required);

const formContext = inject('SuiFormContext', null);

const ptConfig = computed(() => ({
    root: ({ state, props: sProps }) => ({
        class: [
            'sui-select',
            { 'sui-select--open': state.overlayVisible },
            { 'sui-select--error': showError.value },
            { 'sui-select--disabled': sProps.disabled },
        ],
    }),
    label: { class: 'sui-select-display' },
    dropdown: { class: 'sui-select-dropdown' },
    overlay: { class: 'sui-select-overlay' },
    list: { class: 'sui-select-list' },
    option: ({ context }) => ({
        class: [
            'sui-select-option',
            { 'sui-select-option--selected': context.selected },
            { 'sui-select-option--focused': context.focused },
            { 'sui-select-option--disabled': context.disabled },
        ],
    }),
    header: { class: 'sui-select-filter-box' },
    pcFilterContainer: {
        root: { class: 'sui-select-filter-wrapper' }
    },
    pcFilter: {
        root: { class: 'sui-select-filter-field' }
    },
    pcFilterIconContainer: {
        root: { class: 'sui-select-filter-icon-zone' }
    },


    emptyMessage: { class: 'sui-select-empty' },
}));

const validateField = () => {
    internalError.value = '';

    if (props.required) {
        const isEmpty = model.value === undefined || model.value === null || model.value === '';
        if (isEmpty) {
            internalError.value = 'Dieses Feld darf nicht leer sein.';
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
        .sui-select {
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
}

.sui-select {
    display: inline-flex;
    height: 32px;
    background: #fff;
    border: 1px solid var(--color--content-box-border, #d0d2d3);
    border-radius: 4px;
    cursor: pointer;
    align-items: stretch;
    width: 100%;

    &:hover:not(.sui-select--disabled) {
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
        box-shadow: none;
    }

    &--disabled {
        background: var(--color--gray-1, #f1f2f3);
        cursor: not-allowed;
        opacity: 0.6;
    }

    &-display {
        padding: 0 12px;
        font-size: 14px;
        line-height: 30px;
        color: var(--color--font-primary, #333);
        flex: 1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;

        &.p-placeholder {
            color: #7d7f80;
        }
    }

    &-button {
        width: 28px;
        background: var(--color--gray-1, #f1f2f3);
        border-left: 1px solid var(--color--content-box-border, #d0d2d3);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--color--highlight, #28497c);
    }

    .sui-select-dropdown {
        width: 2em;
        display: flex;
        justify-content: center;
        align-items: center;
    }
}

.sui-select-overlay {
    background: #fff;
    border: 1px solid var(--color--highlight, #28497c);
    border-radius: 0 0 4px 4px;
    z-index: 3000;
    margin-top: -3px;

    .sui-select-filter-box {
        padding: 6px;
        background: #fff;
        border-bottom: 1px solid var(--color--content-box-border, #d0d2d3);
        width: 100%;
    }

    .sui-select-filter-wrapper {
        position: relative;
        display: flex;
        align-items: center;
        width: 100%;

        .sui-select-filter-field {
            width: 100%;
            padding: 6px 32px 6px 12px;
            border: 1px solid var(--color--content-box-border, #d0d2d3);
            border-radius: 4px;
            font-size: 13px;
            outline: none;
            background: #fff;
            color: var(--color--font-primary, #333);
            transition: border-color 0.15s ease-in-out;

            &:focus {
                border-color: var(--color--highlight, #28497c);
            }
        }

        // Sauber über das Pass-Through-System gestylt
        .sui-select-filter-icon-zone {
            position: absolute;
            right: 10px;
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

    .sui-select-list {
        list-style: none;
        padding: 0;
        margin: 0;
        max-height: 250px;
        overflow-y: auto;
    }

    .sui-select-option {
        padding: 6px 12px;
        font-size: 14px;
        cursor: pointer;
        color: var(--color--font-primary, #333);
        transition: background-color 0.1s ease;

        &--focused {
            background: #e7ebf1;
        }

        &--selected {
            background: var(--color--highlight, #28497c);
            color: #fff;
        }

        &--disabled {
            opacity: 0.5;
            cursor: not-allowed;
            background: none !important;
        }

        &:hover:not(.sui-select-option--selected):not(.sui-select-option--disabled) {
            background: #e7ebf1;
            color: var(--color--highlight, #28497c);
        }
    }

    .sui-select-empty {
        padding: 8px 12px;
        font-size: 13px;
        color: #7d7f80;
        font-style: italic;
    }
}

.sui-select-filter-box {
    padding: 6px;
    background: var(--color--gray-1, #f1f2f3);
    border-bottom: 1px solid var(--color--content-box-border, #d0d2d3);

    .sui-select-filter-field {
        width: 100%;
        padding: 4px 8px;
        border: 1px solid var(--color--content-box-border, #d0d2d3);
        border-radius: 4px;
        font-size: 13px;
        outline: none;
        background: #fff;

        &:focus {
            border-color: var(--color--highlight, #28497c);
        }
    }
}

.sui-select-footer {
    border-top: 1px solid var(--color--content-box-border, #d0d2d3);
    background: var(--color--gray-1, #f1f2f3);
    padding: 4px;
}

.p-hidden-accessible {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    border: 0;
}
</style>
