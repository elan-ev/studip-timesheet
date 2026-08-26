<template>
    <div class="sui-state-visual" :class="[`sui-state-visual--${type}`]">
        <div class="sui-state-visual-blobs">
            <div class="sui-state-visual-blob sui-state-visual-blob--back"></div>
            <div class="sui-state-visual-blob sui-state-visual-blob--front"></div>
        </div>
        
        <div class="sui-state-visual-icon-wrapper">
            <StudipIcon :shape="iconName" size="128" class="sui-state-visual-icon" />
        </div>
    </div>
</template>

<script setup>
import StudipIcon from './StudipIcon.vue';

defineProps({
    type: {
        type: String,
        default: 'primary',
        validator: (val) => ['primary', 'success', 'warning', 'error', 'info'].includes(val)
    },
    iconName: {
        type: String,
        required: true
    }
})
</script>

<style lang="scss">
.sui-state-visual {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 240px;
    height: 240px;
    margin: 0 auto;

    &-blobs {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    &-blob {
        position: absolute;
        transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        
        &--back {
            width: 220px;
            height: 210px;
            border-radius: 30% 70% 50% 50% / 50% 30% 70% 50%;
            opacity: 0.1;
            transform: rotate(-10deg);
        }

        &--front {
            width: 200px;
            height: 205px;
            border-radius: 60% 40% 30% 70% / 60% 30% 70% 40%;
            opacity: 0.15;
            transform: rotate(15deg);
        }

        .sui-state-visual--primary & { background-color: var(--color--brand-primary, #28497c); }
        .sui-state-visual--success & { background-color: var(--color--good, #6ead10); }
        .sui-state-visual--warning & { background-color: var(--color--attention, #ffbc33); }
        .sui-state-visual--error   & { background-color: var(--color--warning, #d60000); }
        .sui-state-visual--info    & { background-color: var(--color--info, #36598f); }
    }

    &-icon-wrapper {
        position: relative;
        z-index: 2;
        color: #1a1a1b;
        opacity: 0.7;
        
        .sui-state-visual--primary & { color: var(--color--brand-primary, #28497c); }
        .sui-state-visual--success & { color: var(--color--good, #6ead10); }
        .sui-state-visual--warning & { color: var(--color--attention, #ffbc33); }
        .sui-state-visual--error   & { color: var(--color--warning, #d60000); }
        .sui-state-visual--info    & { color: var(--color--info, #36598f); }
    }
}
</style>