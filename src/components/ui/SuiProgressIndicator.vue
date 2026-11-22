<template>
    <div
        class="sui-progress-indicator"
        :class="{ 'sui-progress-indicator--loading-content': withContentSpacing }"
        role="status"
        aria-live="polite"
    >
        <div :style="computedColorStyle">
            <SuiProgressSpinner
                :size="size"
                :duration="duration"
            />
        </div>
        <p v-if="$slots.default || description?.trim()" class="sui-progress-indicator--description">
            <slot>
                {{ description }}
            </slot>
        </p>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import SuiProgressSpinner from './SuiProgressSpinner.vue';

defineOptions({ name: 'SuiProgressIndicator' });

const props = defineProps({
    description: {
        type: String,
        default: '',
    },
    size: {
        type: [Number, String],
        default: 32,
    },
    duration: {
        type: Number,
        default: 1.2,
    },
    spinnerColorHex: {
        type: String,
        default: '',
        validator: (value) => value === '' || /^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$/.test(value),
    },
    spinnerColor: {
        type: String,
        default: 'highlight'
    },
    currentColor: {
        type: Boolean,
        default: false
    },
    withContentSpacing: {
        type: Boolean,
        default: false
    }
});

const computedColorStyle = computed(() => {
    let color = props.spinnerColor ? `var(--color--${props.spinnerColor})` : 'currentColor';
    if (props.currentColor) {
        color = 'currentColor';
    } else if (props.spinnerColorHex) {
        color = props.spinnerColorHex;
    }
    return `color: ${color}`;
});

</script>

<style lang="scss">
.sui-progress-indicator {
    --sui-progress-indicator-padding: 1.5rem 1rem;
    --sui-progress-indicator-gap: 0.75rem;
    --sui-progress-indicator-font-size: 0.95rem;
    --sui-progress-indicator-font-color: #a7abaf;
    --sui-progress-indicator-loading-content-mt: 76px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 100%;
    padding: var(--sui-progress-indicator-padding);
    gap: var(--sui-progress-indicator-gap);
    text-align: center;
    box-sizing: border-box;

    &.sui-progress-indicator--loading-content {
        margin-top: var(--sui-progress-indicator-loading-content-mt);
    }

    .sui-progress-indicator--description {
        margin: 0;
        font-size: var(--sui-progress-indicator-font-size);
        color: var(--sui-progress-indicator-font-color);
    }
}
</style>
