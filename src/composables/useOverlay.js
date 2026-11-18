import { computed } from 'vue';
import { arrow, useFloating, offset, flip, shift, autoUpdate } from '@floating-ui/vue';
import { onClickOutside, useDraggable } from '@vueuse/core';

export const OVERLAY_PLACEMENTS = [
    'top',
    'bottom',
    'left',
    'right',
    'top-start',
    'top-end',
    'bottom-start',
    'bottom-end',
    'left-start',
    'left-end',
    'right-start',
    'right-end',
];

export const DRAWER_SIDES = [
    'top',
    'bottom',
    'left',
    'right',
];

export const FOCUS_SELECTORS = 'button, a[href], input, select, textarea, [tabindex]:not([tabindex="-1"])';

export const DIALOG_VARIANTS = [
    'default',
    'info',
    'warning',
    'error',
    'success',
];

export const RESIZABLE_DIRECTIONS = ['n', 'e', 's', 'w', 'ne', 'nw', 'se', 'sw'];

export function useOverlay(wrapperRef, floatingRef, isOpen, initPlacement, fnClickOutside, arrowRef = null, middlewares = null, options = {}) {

    let defaultMiddlewares = [
        offset(8), flip(), shift({ padding: 8 }), arrow({ element: arrowRef, padding: 10 })
    ];

    let finalMiddlewares = middlewares ?? defaultMiddlewares

    const { floatingStyles, middlewareData, placement } = useFloating(wrapperRef, floatingRef, {
        placement: initPlacement,
        whileElementsMounted: autoUpdate,
        middleware: finalMiddlewares,
        ...options
    });

    const arrowSide = computed(() => {
        return {
            top: 'bottom',
            bottom: 'top',
            left: 'right',
            right: 'left'
        }[placement.value.split('-')[0]]
    })

    const arrowStyles = computed(() => {
        const arrowData = middlewareData.value?.arrow
        if (!arrowData) return {}

        return {
            left:  arrowData.x != null ? `${arrowData.x}px` : '',
            top:  arrowData.y != null ? `${arrowData.y}px` : '',
            [arrowSide.value]: '-5px',
        }
    })

    onClickOutside(wrapperRef, () => {
        if (isOpen.value) {
            fnClickOutside()
        }
    });

    return {
        floatingStyles,
        arrowStyles,
        arrowSide
    };
}

export function useSuiDraggable(floatingRef, handleRef, containerRef, initialX, initialY, disabled = false, fnOnMove = undefined) {
    const { x, y, position, isDragging, style } = useDraggable(floatingRef, {
        handle: handleRef,
        containerElement: containerRef,
        initialValue: {
            x: typeof initialX === 'function' ? initialX() : initialX,
            y: typeof initialY === 'function' ? initialY() : initialY,
        },
        disabled,
        // capture: true,
        onMove: fnOnMove
    });

    return {
        x,
        y,
        position,
        isDragging,
        style
    };
}
