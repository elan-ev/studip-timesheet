<template>
    <div :class="['sui-field-group', { 'sui-field-group--invalid': showError }]">
        
        <label 
            class="sui-switch" 
            :class="[
                `sui-switch--align-${labelAlign}`,
                { 'sui-switch--disabled': disabled }
            ]"
        >
            <span v-if="label && labelAlign === 'left'" class="sui-switch-label">
                {{ label }}
                <span v-if="isRequired" class="sui-field-required" aria-hidden="true">*</span>
            </span>

            <span class="sui-switch-input-wrapper">
                <input 
                    type="checkbox" 
                    role="switch"
                    :id="uuid"
                    class="sui-switch__native"
                    :value="value" 
                    :checked="isChecked" 
                    :disabled="disabled"
                    :aria-invalid="showError ? 'true' : undefined"
                    @change="handleChange"
                />
                <span class="sui-switch-slider">
                    <span class="sui-switch-knob">
                        <StudipIcon :shape="isChecked ? activeIcon : inactiveIcon" size="12" />
                    </span>
                </span>
            </span>

            <span v-if="label && labelAlign === 'right'" class="sui-switch-label">
                {{ label }}
                <span v-if="isRequired" class="sui-field-required" aria-hidden="true">*</span>
            </span>
        </label>

        <span v-if="showError" :id="`${uuid}-error`" class="sui-input-error-message">
            {{ internalError }}
        </span>
        <span v-if="!showError && hint" :id="`${uuid}-hint`" class="sui-input-hint">
            {{ hint }}
        </span>
    </div>
</template>

<script setup>
import { ref, computed, useId, inject, onMounted, onBeforeUnmount } from 'vue';
import StudipIcon from './StudipIcon.vue';

defineOptions({ 
    name: 'SuiSwitch', 
    isSuiFormField: true 
});

const props = defineProps({
    label: { type: String, default: '' },
    value: { type: [String, Number, Boolean], default: true },
    disabled: { type: Boolean, default: false },
    required: { type: Boolean, default: false },
    hint: { type: String, default: '' },
    activeIcon: { type: String, default: 'accept' },
    inactiveIcon: { type: String, default: 'decline' },
    labelAlign: {
        type: String,
        default: 'left',
        validator: (val) => ['left', 'right'].includes(val)
    }
});

const uuid = useId();
const model = defineModel({ default: false });
const formContext = inject('SuiFormContext', null);

const internalError = ref('');
const wasValidated = ref(false);

const isRequired = computed(() => props.required);
const showError = computed(() => wasValidated.value && !!internalError.value);

const isChecked = computed(() => model.value === props.value);

const handleChange = () => {
    model.value = isChecked.value ? false : props.value;
    if (wasValidated.value) {
        internalError.value = '';
    }
};

const formInterface = {
    label: props.label,
    validate: () => {
        wasValidated.value = true;
        if (props.required && !isChecked.value) {
            internalError.value = 'Dieses Feld ist obligatorisch.';
            return false;
        }
        return true;
    },
    getError: () => internalError.value,
    reset: () => {
        internalError.value = '';
        wasValidated.value = false;
        model.value = false;
    }
};

onMounted(() => formContext?.registerField(formInterface));
onBeforeUnmount(() => formContext?.unregisterField(formInterface));
</script>
<style lang="scss">
.sui-switch {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    user-select: none;
    padding: 4px 0;

    &--align-left {
        justify-content: space-between;
        width: 100%;
    }

    &__native {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border-width: 0;

        &:focus-visible + .sui-switch-slider {
            outline: 2px solid var(--color--focus);
            outline-offset: 2px;
        }
    }

    &-slider {
        --sui-switch-width: 40px;
        --sui-switch-height: 20px;

        position: relative;
        display: flex;
        align-items: center;
        width: var(--sui-switch-width);
        height: var(--sui-switch-height);
        background-color: #e2e3e4;
        border-radius: var(--sui-switch-height);
        transition: background-color 0.2s ease-in-out;
        flex-shrink: 0;

        .sui-switch__native:checked + & {
            background-color: #8bbd40;
        }
    }

    &-knob {
        position: absolute;
        left: 2px;
        width: 16px;
        height: 16px;
        background-color: #ffffff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.2s ease-in-out;

        .sui-switch__native:checked + .sui-switch-slider & {
            transform: translateX(20px);
        }
    }

    &-label {
        color: var(--color--font-primary);
        font-size: 0.95rem;
    }

    &--disabled {
        opacity: 0.5;
        cursor: not-allowed;

        .sui-switch-slider {
            background-color: #e2e3e4;
        }
    }
}
</style>