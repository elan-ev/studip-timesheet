<template>
    <button 
        :type="type" 
        class="sui-button" 
        :class="[
            `sui-button--${variant}`,
            `sui-button--size-${effectiveSize}`,
            {
                'sui-button--icon-only': iconOnly,
                'sui-button--icon-only-unstyled': iconOnlyUnstyled,
                'sui-button--is-loading': loading,
                'sui-button--square': square,
                disabled: isEffectiveDisabled,
            },
        ]" 
        :disabled="isEffectiveDisabled || loading" 
        :aria-busy="loading" 
        :aria-label="computedAriaLabel"
        :title="computedTitle" 
        @click="onClick"
    >
        <span 
            v-if="loading || $slots['icon-before'] || (icon && (iconPos === 'left' || square))"
            class="sui-button--visual sui-button--visual-left"
        >
            <SuiProgressSpinner 
                v-if="loading" 
                :size="iconSizeNumber" 
                aria-hidden="true"
                class="sui-button--icon-loading" 
            />
            <slot v-else name="icon-before">
                <StudipIcon 
                    v-if="icon && (iconPos === 'left' || square)" 
                    :shape="icon" 
                    :size="iconSize" 
                    aria-hidden="true"
                    class="sui-button--icon" 
                />
            </slot>
        </span>

        <span 
            v-if="label || $slots.default" 
            class="sui-button--label" 
            :class="{ 'sr-only': iconOnly }"
        >
            <slot>{{ label }}</slot>
        </span>

        <span 
            v-if="!square && !loading && ($slots['icon-after'] || (icon && iconPos === 'right'))"
            class="sui-button--visual sui-button--visual-right"
        >
            <slot name="icon-after">
                <StudipIcon 
                    v-if="icon && iconPos === 'right'" 
                    :shape="icon" 
                    :size="iconSize" 
                    aria-hidden="true"
                    class="sui-button--icon" 
                />
            </slot>
        </span>
    </button>
</template>

<script setup>
import { inject, computed } from 'vue'
import StudipIcon from './StudipIcon.vue'
import SuiProgressSpinner from './SuiProgressSpinner.vue'

const props = defineProps({
    label: { type: String, required: true },
    type: { type: String, default: 'button' },
    icon: { type: String, default: null },
    iconPos: {
        type: String,
        default: 'left',
        validator: (val) => ['left', 'right'].includes(val),
    },
    iconOnly: { type: Boolean, default: false },
    iconOnlyUnstyled: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
    loading: { type: Boolean, default: false },
    square: { type: Boolean, default: false },
    size: {
        type: String,
        default: 'normal',
        validator: (val) => ['small', 'normal', 'large'].includes(val),
    },
    variant: {
        type: String,
        default: 'default',
        validator: (val) =>
            [
                'default',
                'primary',
                'secondary',
                'success',
                'warning',
                'danger',
                'promoted',
                'text',
                'clean'
            ].includes(val),
    },
    ariaLabel: { type: String, default: undefined },
})

const groupContext = inject('sui-button-group-context', null)

const emit = defineEmits(['click'])

const computedAriaLabel = computed(() => {
    if (props.iconOnly) {
        return undefined
    }
    if (props.ariaLabel) {
        return props.ariaLabel
    }
    return undefined
})

const computedTitle = computed(() => {
    if (props.iconOnly) {
        return props.label
    }
    return undefined
})

const iconSizeNumber = computed(() => {
    if (props.square) {
        if (props.size === 'small') return 18
        if (props.size === 'large') return 75
        return 50
    }
    if (props.size === 'small') return 14
    if (props.size === 'large') return 24
    return 16
})

const iconSize = computed(() => String(iconSizeNumber.value))

const effectiveSize = computed(() => {
    return groupContext?.value?.size || props.size
})

const isEffectiveDisabled = computed(() => {
    return props.disabled || groupContext?.value?.disabled || false
})

const onClick = (event) => {
    if (!props.disabled && !props.loading) {
        emit('click', event)
    }
}
</script>

<style lang="scss" scoped>
.sui-button {
    --sui-button-bg: var(--comp-button-default-bg, #fff);
    --sui-button-border-color: var(--comp-button-default-border-color, #28497c);
    --sui-button-color: var(--comp-button-default-color, #28497c);
    --sui-button-border-style: var(--comp-button-default-border-style, solid);
    --sui-button-border-width: var(--comp-button-default-border-width, 1px);
    --sui-button-cursor: pointer;
    --sui-button-justify: center;
    --sui-button-gap: var(--comp-button-md-gap, 7px);
    --sui-button-aspect-ratio: auto;
    --sui-button-icon-display: inline-flex;
    --sui-button-text-decoration-hover: none;
    --sui-button-padding: var(--comp-button-md-padding-block, 7px) var(--comp-button-md-padding-inline, 14px);
    --sui-button-font-size: var(--comp-button-md-font-size, 14px);
    --sui-button-min-width: 100px;
    --sui-button-line-height: var(--comp-button-md-line-height, 1.4285714286);

    --sui-button-color-hover: var(--comp-button-default-hover-color, #fff);
    --sui-button-bg-hover: var(--comp-button-default-hover-bg, #28497c);
    --sui-button-border-color-hover: var(--comp-button-default-hover-border-color, #28497c);

    --sui-button-square-size: var(--comp-button-square-md-size, 130px);

    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: var(--sui-button-justify);
    gap: var(--sui-button-gap);
    margin: 0;
    font-family: inherit;
    border-radius: 4px;
    white-space: nowrap;
    transition: background-color 0.2s, color 0.2s, border-color 0.2s;
    
    padding: var(--sui-button-padding);
    font-size: var(--sui-button-font-size);
    min-width: var(--sui-button-min-width);
    aspect-ratio: var(--sui-button-aspect-ratio);
    cursor: var(--sui-button-cursor);
    
    background-color: var(--sui-button-bg);
    border-width: var(--sui-button-border-width);
    border-style: var(--sui-button-border-style);
    border-color: var(--sui-button-border-color);
    color: var(--sui-button-color);

    &:hover:not(:disabled):not(.disabled) {
        background-color: var(--sui-button-bg-hover);
        border-color: var(--sui-button-border-color-hover);
        color: var(--sui-button-color-hover);
        text-decoration: var(--sui-button-text-decoration-hover);
    }

    &--icon {
        flex-shrink: 0;
        display: var(--sui-button-icon-display);
        align-items: center;
        justify-content: center;
    }

    &--visual {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    &--label {
        display: inline-block;
        vertical-align: middle;
        line-height: var(--sui-button-line-height);

        &.sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            border: 0;
        }
    }

    &--size-small {
        --sui-button-padding: var(--comp-button-sm-padding-block) var(--comp-button-sm-padding-inline);
        --sui-button-font-size: var(--comp-button-sm-font-size);
        --sui-button-min-width: 60px;
        --sui-button-line-height: var(--comp-button-sm-line-height);
    }

    &--size-large {
        --sui-button-padding: var(--comp-button-lg-padding-block) var(--comp-button-lg-padding-inline);
        --sui-button-font-size: var(--comp-button-lg-font-size);;
        --sui-button-min-width: 140px;
        --sui-button-line-height: var(--comp-button-lg-line-height);
    } 

    &--square {
        display: flex;
        flex-direction: column;
        justify-content: var(--sui-button-justify);
        align-items: center;
        --sui-button-aspect-ratio: 1 / 1;
        --sui-button-padding: 10px;
        --sui-button-gap: 8px;

        max-height: var(--sui-button-square-size);
        max-width: var(--sui-button-square-size);
        min-width: var(--sui-button-square-size);
        min-height:var(--sui-button-square-size);

        &.sui-button--size-small {
            --sui-button-font-size: var(--comp-button-sm-font-size);
            --sui-button-gap: var(--comp-button-sm-gap);
            --sui-button-square-size: var(--comp-button-square-sm-size);
        }

        &.sui-button--size-large {
            --sui-button-justify: center;
            --sui-button-font-size: var(--comp-button-lg-font-size);
            --sui-button-gap: var(--comp-button-lg-gap);
            --sui-button-square-size: var(--comp-button-square-lg-size);
        }

        .sui-button--label {
            margin: 4px 0 0 0;
            white-space: normal;
            word-break: break-word;
        }
    }

    $variants: (
        'primary',
        'secondary',
        'warning',
        'success',
        'danger',
        'promoted', 
    );

    @each $name in $variants {
        &--#{$name} {
            --sui-button-bg: var(--comp-button-#{$name}-bg);
            --sui-button-border-color:var(--comp-button-#{$name}-border-color);
            --sui-button-color: var(--comp-button-#{$name}-color);
            
            --sui-button-bg-hover: var(--comp-button-#{$name}-hover-bg);
            --sui-button-border-color-hover: var(--comp-button-#{$name}-hover-border-color);
            --sui-button-color-hover: var(--comp-button-#{$name}-hover-color);
        }
    }

    &.disabled,
    &[disabled],
    &--disabled,
    &--is-loading {
        --sui-button-cursor: not-allowed;
    }

    &.disabled,
    &[disabled] {
        --sui-button-color: var(--comp-button-disabled-color);
        --sui-button-bg: var(--comp-button-disabled-bg);
        --sui-button-border-color: var(--comp-button-disabled-border-color);
        
        --sui-button-bg-hover: var(--comp-button-disabled-bg);
        --sui-button-border-color-hover: var(--comp-button-disabled-border-color);
        --sui-button-color-hover:var(--comp-button-disabled-color);
    }

    &--text {
        --sui-button-bg: transparent;
        --sui-button-border-style: none;
        --sui-button-border-width: 0px;
        --sui-button-min-width: unset;
        --sui-button-aspect-ratio: auto;
        --sui-button-icon-display: none;

        --sui-button-bg-hover: transparent;
        --sui-button-border-color-hover: transparent;
        --sui-button-color-hover: var(--color--highlight-hover);
        --sui-button-text-decoration-hover: underline;

        &.disabled,
        &[disabled] {
            --sui-button-bg: transparent;
            --sui-button-border-color: transparent;
            --sui-button-bg-hover: transparent;
            --sui-button-border-color-hover: transparent;
        }
    }

    &--icon-only {
        --sui-button-min-width: unset;
        --sui-button-padding: 7px;
        --sui-button-gap: 0;
        --sui-button-aspect-ratio: 1 / 1;
    }

    &--icon-only-unstyled {
        --sui-button-bg: transparent;
        --sui-button-border-style: none;
        --sui-button-border-width: 0px;
        --sui-button-padding: 4px;
        --sui-button-min-width: auto;
        --sui-button-aspect-ratio: 1 / 1;

        --sui-button-bg-hover: transparent;
        --sui-button-border-color-hover: transparent;
        --sui-button-color-hover: var(--sui-button-color); 
        
        &.sui-button {
            @each $name, $var in $variants {
               &--#{$name} {
                    --sui-button-color: var(--color--#{$var});
                    --sui-button-color-hover: var(--color--#{$var});
                }
            }
        }
    }
}
</style>