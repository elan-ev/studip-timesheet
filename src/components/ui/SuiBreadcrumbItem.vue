<template>
    <li class="sui-breadcrumb-item" :class="{ 'sui-breadcrumb-item--current': current }">
        <span v-if="showSeparator" class="sui-breadcrumb-item__separator" :class="{'sui-breadcrumb-item__separator--chevron': separator === 'chevron'}" aria-hidden="true">
            <sui-icon
                v-if="separator === 'chevron'"
                shape="bullet-arrow"
                :size="16"
                :inline="true"
                class="sui-breadcrumb-item__separator-icon"
            />
            <template v-else>/</template>
        </span>

        <component
            :is="linkComponent"
            v-if="isLink"
            v-bind="linkAttributes"
            :title="computedTitle"
            class="sui-breadcrumb-item__link"
            @click="handleClick"
        >
            <sui-icon
                v-if="icon"
                :shape="icon"
                :size="16"
                :inline="true"
                class="sui-breadcrumb-item__icon"
            />
            <span :class="{ 'sui-breadcrumb-item__label-hidden': isIconOnly }">{{ label }}</span>
        </component>

        <span v-else class="sui-breadcrumb-item__text" :aria-current="current ? 'page' : undefined" :title="computedTitle">
            <sui-icon
                v-if="icon"
                :shape="icon"
                :size="16"
                :inline="true"
                class="sui-breadcrumb-item__icon"
            />
            <span :class="{ 'sui-breadcrumb-item__label-hidden': isIconOnly }">{{ label }}</span>
        </span>
    </li>
</template>

<script setup>
import { computed } from 'vue'
import SuiIcon from './SuiIcon.vue'

const props = defineProps({
    label: {
        type: String,
        required: true,
    },
    to: {
        type: String,
        default: '',
    },
    linkType: {
        type: String,
        default: 'anchor',
        validator: (value) => ['anchor', 'router'].includes(value),
    },
    separator: {
        type: String,
        default: 'chevron',
        validator: (value) => ['chevron', 'slash'].includes(value),
    },
    showSeparator: {
        type: Boolean,
        default: false,
    },
    icon: {
        type: String,
        default: '',
    },
    iconOnly: {
        type: Boolean,
        default: false,
    },
    current: {
        type: Boolean,
        default: false,
    },
})

const emit = defineEmits(['navigate'])

const useRouter = computed(() => {
    return props.linkType === 'router'
})

const hasTarget = computed(() => {
    return !!props.to
})

const isLink = computed(() => {
    return !props.current && hasTarget.value
})

const linkComponent = computed(() => {
    return useRouter.value ? 'router-link' : 'a'
})

const linkAttributes = computed(() => {
    if (useRouter.value) {
        return { to: props.to }
    }
    return { href: props.to }
})

const isIconOnly = computed(() => {
    return props.iconOnly && !!props.icon
})

const computedTitle = computed(() => {
    return isIconOnly.value ? props.label : undefined
})

const handleClick = (event) => {
    emit('navigate', { originalEvent: event })
}
</script>

<style lang="scss" scoped>
.sui-breadcrumb-item {
    --sui-breadcrumb-item-font-size: var(--comp-menu-text-size-default, 14px);
    --sui-breadcrumb-item-line-height:  var(--comp-menu-text-line-height-breadcrumb, 1.5);
    --sui-breadcrumb-item-current-font-weight: var(--comp-menu-text-weight-emphasis, 700);
    --sui-breadcrumb-item-link-color: var(--comp-menu-text-color-action, #28497c);
    --sui-breadcrumb-item-link-color-hover: var(--comp-menu-text-color-hover, #d60000);
    --sui-breadcrumb-item-link-decoration-hover: underline;
    --sui-breadcrumb-item-separator-gap: var(--comp-menu-container-spacing-inner, 8px);
    --sui-breadcrumb-item-separator-icon-gap: calc(var(--comp-menu-container-spacing-inner, 8px) / 2);
    --sui-breadcrumb-item-separator-color: var(--comp-menu-text-color-subtle, #676767);
    --sui-breadcrumb-item-icon-gap: calc(var(--comp-menu-container-spacing-inner, 8px) / 2);

    display: flex;
    align-items: center;
    font-size: var(--sui-breadcrumb-item-font-size);
    line-height: var(--sui-breadcrumb-item-line-height);

    &__separator {
        margin-inline: var(--sui-breadcrumb-item-separator-gap);
        color: var(--sui-breadcrumb-item-separator-color);
    }

    &__separator--chevron {
        margin-inline: var(--sui-breadcrumb-item-separator-icon-gap);
        img {
            vertical-align: text-bottom;
        }
    }

    &__separator-icon {
        vertical-align: text-bottom;
    }

    &__link {
        color: var(--sui-breadcrumb-item-link-color);
        text-decoration: none;

        &:hover {
            color: var(--sui-breadcrumb-item-link-color-hover);
            text-decoration: var(--sui-breadcrumb-item-link-decoration-hover);
        }
    }

    &__icon {
        margin-inline-end: var(--sui-breadcrumb-item-icon-gap);
        vertical-align: text-bottom;
    }

    &--current &__text {
        font-weight: var(--sui-breadcrumb-item-current-font-weight);
    }

    &__label-hidden {
        position: absolute;
        width: 1px;
        height: 1px;
        overflow: hidden;
        clip-path: inset(50%);
        white-space: nowrap;
    }
}
</style>
