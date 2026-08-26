<template>
    <div class="sui-progress-spinner" :aria-hidden="!$attrs.role ? 'true' : undefined">
        <svg 
            viewBox="0 0 64 64" 
            xmlns="http://www.w3.org/2000/svg" 
            class="sui-progress-spinner__svg"
        >
            <g>
                <animateTransform
                    attributeName="transform"
                    type="rotate"
                    from="0 32 32"
                    to="360 32 32"
                    :dur="svgDuration"
                    repeatCount="indefinite"
                    class="sui-progress-spinner__animation"
                />
                
                <path 
                    fill="currentColor" 
                    d="M32,4C16.5,4,4,16.5,4,32h8.3c0-10.9,8.8-19.7,19.7-19.6c10.9,0,19.7,8.8,19.6,19.7c0,10.9-8.8,19.6-19.7,19.6V60c15.5,0,28-12.5,28-28S47.5,4,32,4z"
                />
                <path 
                    fill="currentColor" 
                    d="M42.5,32c0-5.8-4.7-10.5-10.5-10.5c-5.8,0-10.5,4.7-10.5,10.5c0,5.8,4.7,10.5,10.5,10.5C37.8,42.6,42.5,37.8,42.5,32C42.5,32,42.5,32,42.5,32z"
                />
            </g>
        </svg>
    </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    duration: {
        type: Number,
        default: 1.2,
    },
    size: {
        type: [Number, String],
        default: 32,
    },
    color: {
        type: String,
        default: 'currentColor',
    }
})

const cssSize = computed(() => typeof props.size === 'number' ? `${props.size}px` : props.size)
const svgDuration = computed(() => `${props.duration}s`)
</script>

<style lang="scss">
.sui-progress-spinner {
    display: inline-flex;
    vertical-align: middle;
    flex-shrink: 0;
    line-height: 0;
    
    width: v-bind(cssSize);
    height: v-bind(cssSize);
    color: v-bind(color);
    
    &__svg {
        width: 100%;
        height: 100%;
    }
}

@media (prefers-reduced-motion: reduce) {
    .sui-progress-spinner__animation {
        animation-name: none !important; 
    }
}
</style>