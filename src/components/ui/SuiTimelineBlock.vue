<template>
  <div 
    class="sui-timeline-block"
    :class="[`sui-timeline-block--${type}`]"
    :style="blockStyle"
    :title="tooltipText"
  >
    <span class="sui-timeline-block__label">
      <slot>{{ label }}</slot>
    </span>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  start: {
    type: Date,
    required: true,
  },
  end: {
    type: Date,
    required: true,
  },
  getPosition: {
    type: Function,
    required: true,
  },
  label: {
    type: String,
    default: '',
  },
  type: {
    type: String,
    default: 'work', // 'work', 'pause', 'idle'
  },
})

const left = computed(() => props.getPosition(props.start))
const right = computed(() => props.getPosition(props.end))
const width = computed(() => Math.max(right.value - left.value, 0))

const blockStyle = computed(() => ({
  left: `${left.value}%`,
  width: `${width.value}%`,
}))

const formatTime = (d) => d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })

const tooltipText = computed(() => `${props.label} (${formatTime(props.start)} - ${formatTime(props.end)})`)
</script>

<style lang="scss" scoped>
.sui-timeline-block {
  position: absolute;
  top: 6px;
  bottom: 6px;
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 0.5rem;
  font-size: 0.75rem;
  font-weight: 600;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  transition: all 0.2s ease;

  &--work {
    background: var(--color--primary, #2563eb);
    color: #ffffff;
  }

  &--pause {
    background: repeating-linear-gradient(
      45deg,
      #e2e8f0,
      #e2e8f0 8px,
      #cbd5e1 8px,
      #cbd5e1 16px
    );
    color: var(--color--font-secondary, #475569);
  }
}
</style>