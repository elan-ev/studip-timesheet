<template>
  <div 
    class="sui-progress-ring" 
    :style="{ width: `${size}px`, height: `${size}px` }"
    role="progressbar"
    :aria-valuenow="value"
    :aria-valuemin="0"
    :aria-valuemax="max"
    :aria-valuetext="ariaText"
  >
    <svg :width="size" :height="size" class="sui-progress-ring__svg">
      <circle
        class="sui-progress-ring__track"
        :stroke="resolvedTrackColor"
        :stroke-width="strokeWidth"
        fill="transparent"
        :r="radius"
        :cx="center"
        :cy="center"
      />
      <circle
        class="sui-progress-ring__fill"
        :stroke="resolvedColor"
        :stroke-width="strokeWidth"
        stroke-linecap="round"
        fill="transparent"
        :r="radius"
        :cx="center"
        :cy="center"
        :style="{
          strokeDasharray: `${circumference} ${circumference}`,
          strokeDashoffset: strokeDashoffset,
        }"
      />
    </svg>

    <div class="sui-progress-ring__content">
      <slot :value="value" :max="max" :percentage="percentage">
        <span class="sui-progress-ring__value">{{ value }}</span>
        <span v-if="showRatio" class="sui-progress-ring__ratio">/ {{ max }}</span>
      </slot>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  value: {
    type: Number,
    required: true,
  },
  max: {
    type: Number,
    default: 100,
  },
  size: {
    type: Number,
    default: 80,
  },
  strokeWidth: {
    type: Number,
    default: 8,
  },
  color: {
    type: String,
    default: '#536d96',
  },
  trackColor: {
    type: String,
    default: '#d8d9dc',
  },
  showRatio: {
    type: Boolean,
    default: false,
  },
})

const center = computed(() => props.size / 2)
const radius = computed(() => {
  const r = (props.size - props.strokeWidth) / 2
  return r > 0 ? r : 0
})
const circumference = computed(() => 2 * Math.PI * radius.value)

const percentage = computed(() => {
  if (!props.max || props.max <= 0) return 0
  return Math.min(Math.max(props.value / props.max, 0), 1)
})

const strokeDashoffset = computed(() => {
  return circumference.value - percentage.value * circumference.value
})

const ariaText = computed(() => `${props.value} von ${props.max}`)

const resolvedColor = computed(() => {
  if (props.color.startsWith('#') || props.color.startsWith('rgb') || props.color.startsWith('var(')) {
    return props.color
  }
  return `var(--color--${props.color}, ${props.color})`
})

const resolvedTrackColor = computed(() => {
  if (props.trackColor.startsWith('#') || props.trackColor.startsWith('rgb') || props.trackColor.startsWith('var(')) {
    return props.trackColor
  }
  return `var(--color--${props.trackColor}, #eee)`
})
</script>

<style lang="scss" scoped>
.sui-progress-ring {
  position: relative;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;

  &__svg {
    transform: rotate(-90deg);
  }

  &__fill {
    transition: stroke-dashoffset 0.35s ease-in-out, stroke 0.2s ease;
  }

  &__content {
    position: absolute;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    line-height: 1.1;
    user-select: none;
  }

  &__value {
    font-size: 1rem;
    font-weight: 700;
    color: var(--color--font-primary, #333);
  }

  &__ratio {
    font-size: 0.75rem;
    color: var(--color--font-secondary, #666);
  }
}
</style>