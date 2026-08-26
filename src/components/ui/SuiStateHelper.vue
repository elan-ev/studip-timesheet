<template>
    <div
        class="sui-state-helper"
        :class="[`sui-state-helper--${type}`, { 'sui-state-helper--compact': compact }]"
    >
        <div class="sui-state-helper-visual-container">
            <slot name="visual">
                <SuiStateVisual :type="type" :icon-name="iconName" />
            </slot>
        </div>

        <div class="sui-state-helper-content">
            <h3 v-if="title" class="sui-state-helper-title">{{ title }}</h3>

            <p v-if="description || $slots.default" class="sui-state-helper-description">
                <slot>{{ description }}</slot>
            </p>

            <div v-if="$slots.actions" class="sui-state-helper-actions">
                <slot name="actions"></slot>
            </div>
        </div>
    </div>
</template>

<script setup>
import SuiStateVisual from './SuiStateVisual.vue'

defineProps({
    type: {
        type: String,
        default: 'primary',
        validator: (val) => ['primary', 'success', 'warning', 'error', 'info'].includes(val),
    },
    iconName: { type: String, required: true },
    title: { type: String, default: '' },
    description: { type: String, default: '' },
    compact: { type: Boolean, default: false },
})
</script>
<style lang="scss">
.sui-state-helper {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 3rem 2rem;
    gap: 2rem;
    max-width: 600px;
    margin: 0 auto;

    &--compact {
        flex-direction: row;
        text-align: left;
        padding: 1.5rem;
        gap: 1.5rem;
        max-width: 100%;

        .sui-state-helper-visual-container {
            transform: scale(0.6);
            transform-origin: center;
            margin: -40px;
            flex-shrink: 0;
        }

        .sui-state-helper-content {
            align-items: flex-start;
            text-align: left;
            gap: 0.5rem;
        }

        .sui-state-helper-title {
            font-size: 1.15rem;
            line-height: 1.3;
        }

        .sui-state-helper-description {
            font-size: 0.95rem;
            line-height: 1.4;
        }

        .sui-state-helper-actions {
            justify-content: flex-start;
            margin-top: 0.5rem;
        }
    }

    &-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1rem;
    }

    &-title {
        margin: 0;
        font-size: 28px;
        font-weight: 600;
        color: var(--color--font-base);
        transition: font-size 0.2s ease;
    }

    &-description {
        margin: 0;
        font-size: 16px;
        line-height: 1.6;
        color: var(--color--font-light);
        transition: font-size 0.2s ease;
    }

    &-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        justify-content: center;
        margin-top: 1rem;
    }
}
</style>
