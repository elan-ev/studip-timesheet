<template>
    <div ref="wrapperRef" class="sui-drawer">
        <div
            class="sui-drawer--anchor"
            :aria-controls="isOpen ? drawerId : undefined"
            @click="toggle"
        >
            <slot name="anchor" :is-open="isOpen" :toggle="toggle" />
        </div>

        <Transition name="sui-drawer-slide">
            <Teleport v-if="isOpen" :to="appendTo">
                <div ref="floatingRef" class="sui-drawer--container">
                    <div
                        v-if="withOverlay"
                        class="sui-drawer--overlay"
                        @click="onOverlayClick"
                    />
                    <FocusTrap :active="false" ref="focusTrapRef">
                        <div
                            :id="drawerId"
                            role="dialog"
                            aria-modal="true"
                            tabindex="-1"
                            class="sui-drawer--content"
                            :class="[
                                `sui-drawer--${side}`,
                                { 'sui-drawer--full': fullscreen },
                                customClasses
                            ]"
                            :style="drawerStyle"
                            @keydown.escape="close"
                            :aria-labelledby="title ? `${drawerId}-title` : undefined"
                        >
                            <header
                                v-if="$slots.header || title || showCloseButton"
                                class="sui-drawer--header"
                            >

                                <slot name="header">
                                    <h3 v-if="title" :id="`${drawerId}-title`" class="sui-drawer--title"> {{ title }} </h3>
                                </slot>

                                <SuiButton
                                    v-if="showCloseButton"
                                    class="sui-drawer--close-btn"
                                    icon="decline"
                                    :icon-only="true"
                                    :icon-only-unstyled="true"
                                    :aria-label="$gettext('Schließen')"
                                    :label="$gettext('Schließen')"
                                    @click="close"
                                />
                            </header>

                            <div class="sui-drawer--body">
                                <slot :close="close" />
                            </div>

                            <div v-if="$slots.footer" class="sui-drawer--footer">
                                <slot name="footer" :close="close" />
                            </div>
                        </div>
                    </FocusTrap>
                </div>
            </Teleport>
        </Transition>
    </div>
</template>

<script setup>
import { ref, nextTick, useTemplateRef, onUnmounted, computed, getCurrentInstance, useId } from 'vue'
import SuiButton from './SuiButton.vue'
import { FocusTrap } from 'focus-trap-vue'
import { DRAWER_SIDES, FOCUS_SELECTORS } from './../../composables/'

const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false,
    },
    side: {
        type: String,
        default: 'right',
        validator: (value) => DRAWER_SIDES.includes(value),
    },
    title: {
        type: String,
        default: '',
    },
    withOverlay: {
        type: Boolean,
        default: true,
    },
    dismissible: {
        type: Boolean,
        default: true,
    },
    showCloseButton: {
        type: Boolean,
        default: true,
    },
    fullscreen: {
        type: Boolean,
        default: false,
    },
    appendTo: {
        type: String,
        default: 'body',
    },
    drawerId: {
        type: String,
        default: () => `sui-drawer-${useId()}`,
    },
    focusTargetId: {
        type: String,
        default: '',
    },
    customClasses: {
        type: String,
        default: '',
    },
    width: {
        type: [Number, String],
        default: '270',
    },
    maxWidth: {
        type: Number,
        required: false,
    }
})

const emit = defineEmits(['update:modelValue', 'open', 'close'])

const { proxy } = getCurrentInstance();
const $gettext = proxy?.$gettext ?? ((str) => str);

const focusTrapRef = useTemplateRef('focusTrapRef')
const wrapperRef = useTemplateRef('wrapperRef')
const floatingRef = useTemplateRef('floatingRef')
const isOpen = ref(false)

let focusTrapTimer = null
let focusTimer = null;
let lastFocusedElement = null

const drawerStyle = computed(() => {
    const style = {};
    if (!props.fullscreen) {
        if (typeof props.width === 'number') {
            style.width = props.width + 'px';
        } else {
            style.width = props.width;
        }

        if (props.maxWidth) {
            style.maxWidth = props.maxWidth + 'px';
        }
    }

    return style;
});

const open = () => {
    if (isOpen.value) return
    isOpen.value = true
    emit('update:modelValue', true)
    emit('open')

    lastFocusedElement = document.activeElement

    nextTick(() => {

        clearTimeout(focusTrapTimer)
        focusTrapTimer = setTimeout(() => {
            focusTrapRef.value?.activate()
        }, 300)

        if (floatingRef.value) {
            focusTimer = setTimeout(() => {
                const targetFocusable = props.focusTargetId ? floatingRef.value.querySelector(`#${props.focusTargetId}`) : null;
                if (targetFocusable) {
                    targetFocusable.focus()
                    return
                }

                const body = floatingRef.value.querySelector('.sui-drawer--body')
                const bodyFocusable = body ? body.querySelector(FOCUS_SELECTORS) : null
                if (bodyFocusable) {
                    bodyFocusable.focus()
                    return
                }

                let defaultFocusable = floatingRef.value.querySelector(FOCUS_SELECTORS) || floatingRef.value
                if (defaultFocusable) {
                    defaultFocusable.focus()
                    return
                }
            }, 300);
        }
    })
}

const close = () => {
    if (!isOpen.value) return
    if (focusTrapTimer) {
        clearTimeout(focusTrapTimer)
    }
    if (focusTimer) {
        clearTimeout(focusTimer)
    }
    isOpen.value = false
    emit('update:modelValue', false)
    emit('close')
    focusTrapRef.value?.deactivate()

    nextTick(() => {
        if (lastFocusedElement && typeof lastFocusedElement.focus === 'function') {
            lastFocusedElement.focus()
        }
    })
}

const toggle = () => {
    isOpen.value ? close() : open()
}

const onOverlayClick = () => {
    if (props.dismissible) {
        close()
    }
}

onUnmounted(() => {
    if (focusTrapTimer) {
        clearTimeout(focusTrapTimer)
    }
    if (focusTimer) {
        clearTimeout(focusTimer)
    }
})

defineExpose({
    open,
    close,
    toggle,
    isOpen,
})

</script>

<style lang="scss">
/* Global vars */
$sui-drawer-transition-transform: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
$sui-drawer-transition-opacity: opacity 0.3s ease;

.sui-drawer {
    display: inline-block;
}

.sui-drawer--anchor {
    display: inline-flex;
    cursor: pointer;
}

.sui-drawer--container {
    --sui-drawer-overlay-bg: rgba(0, 0, 0, 0.4);
    --sui-drawer-bg: #ffffff;
    --sui-drawer-text-color: #1e293b;
    --sui-drawer-border-color: #e2e8f0;
    --sui-drawer-shadow: -4px 0 16px rgba(0, 0, 0, 0.15);
    --sui-drawer-z-index: 1200;

    .sui-drawer--overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background-color: var(--sui-drawer-overlay-bg);
        z-index: var(--sui-drawer-z-index);
        transition: $sui-drawer-transition-opacity;
    }

    .sui-drawer--content {
        position: fixed;
        background-color: var(--sui-drawer-bg);
        color: var(--sui-drawer-text-color);
        box-shadow: var(--sui-drawer-shadow);
        z-index: calc(var(--sui-drawer-z-index) + 1);
        display: flex;
        flex-direction: column;
        outline: none;
        transition: $sui-drawer-transition-transform;

        /* Sliding Positions */
        &.sui-drawer--right {
            top: 0;
            right: 0;
            // width: 28rem;
            // max-width: 100vw;
            height: 100vh;
        }

        &.sui-drawer--left {
            top: 0;
            left: 0;
            width: 28rem;
            max-width: 100vw;
            height: 100vh;
        }

        &.sui-drawer--top {
            top: 0;
            left: 0;
            width: 100vw;
            height: 20rem;
            max-height: 100vh;
        }

        &.sui-drawer--bottom {
            bottom: 0;
            left: 0;
            width: 100vw;
            height: 20rem;
            max-height: 100vh;
        }

        &.sui-drawer--full {
            top: 0;
            left: 0;
            width: 100vw !important;
            height: 100vh !important;
        }

        .sui-drawer--header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--sui-drawer-border-color);
        }

        .sui-drawer--title {
            margin: 0;
            font-size: 1.125rem;
            font-weight: 600;
        }

        .sui-drawer--body {
            flex: 1;
            padding: 1.25rem;
            overflow-y: auto;
        }

        .sui-drawer--footer {
            padding: 1rem 1.25rem;
            border-top: 1px solid var(--sui-drawer-border-color);
            display: flex;
            justify-content: flex-end;
            gap: 0.5rem;
        }
    }
}

/* Transitions */
.sui-drawer-slide-enter-active,
.sui-drawer-slide-leave-active {
    transition: $sui-drawer-transition-opacity;

    .sui-drawer--content {
        transition: $sui-drawer-transition-transform;
    }
}

.sui-drawer-slide-enter-from,
.sui-drawer-slide-leave-to {
    opacity: 0;

    .sui-drawer--right {
        transform: translateX(100%);
    }

    .sui-drawer--left {
        transform: translateX(-100%);
    }

    .sui-drawer--top {
        transform: translateY(-100%);
    }

    .sui-drawer--bottom {
        transform: translateY(100%);
    }
}
</style>
