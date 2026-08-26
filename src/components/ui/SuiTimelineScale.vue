<template>
  <div class="sui-timeline-scale">
    <div class="sui-timeline-scale__header">
      <div 
        v-for="hour in hours" 
        :key="hour" 
        class="sui-timeline-scale__hour-tick"
      >
        <span>{{ String(hour).padStart(2, '0') }}:00</span>
      </div>
    </div>

    <div class="sui-timeline-scale__track">
      <div class="sui-timeline-scale__grid">
        <div v-for="hour in hours" :key="hour" class="sui-timeline-scale__grid-line" />
      </div>

      <div class="sui-timeline-scale__blocks">
        <slot :get-position="getPosition" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  startHour: {
    type: Number,
    default: 6,
  },
  endHour: {
    type: Number,
    default: 22,
  },
})

const hours = computed(() => {
  const list = []
  for (let h = props.startHour; h <= props.endHour; h++) {
    list.push(h)
  }
  return list
})

const getPosition = (date) => {
  if (!(date instanceof Date) || isNaN(date)) return 0

  const startMinutes = props.startHour * 60
  const endMinutes = props.endHour * 60
  const totalMinutes = endMinutes - startMinutes

  const currentMinutes = date.getHours() * 60 + date.getMinutes()
  const offset = currentMinutes - startMinutes

  const percentage = (offset / totalMinutes) * 100
  return Math.min(Math.max(percentage, 0), 100)
}
</script>

<style lang="scss" scoped>
.sui-timeline-scale {
  display: flex;
  flex-direction: column;
  width: 100%;
  user-select: none;

  &__header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
  }

  &__hour-tick {
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--color--font-secondary, #64748b);
    text-align: center;
    flex: 1;
    &:first-child { text-align: left; }
    &:last-child { text-align: right; }
  }

  &__track {
    position: relative;
    height: 48px;
    background: var(--color--black-5, #f8fafc);
    border: 1px solid var(--color--content-border, #e2e8f0);
    border-radius: var(--border-radius--base, 6px);
    overflow: hidden;
  }

  &__grid {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    display: flex;
    justify-content: space-between;
    pointer-events: none;
  }

  &__grid-line {
    width: 1px;
    height: 100%;
    background: var(--color--content-border, #e2e8f0);
    opacity: 0.5;
  }

  &__blocks {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
  }
}
</style>