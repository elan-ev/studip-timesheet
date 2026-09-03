<template>
  <div class="timeline-scale">
    <div class="timeline-scale__header">
      <span 
        v-for="(hour, index) in hours" 
        :key="hour" 
        class="timeline-scale__hour-tick"
        :style="{ left: `${(index / totalHours) * 100}%` }"
      >
        {{ String(hour).padStart(2, '0') }}:00
      </span>
    </div>

    <div class="timeline-scale__content">
      <div class="timeline-scale__grid">
        <div 
          v-for="(hour, index) in hours" 
          :key="hour" 
          class="timeline-scale__grid-line"
          :style="{ left: `${(index / totalHours) * 100}%` }"
        />
      </div>

      <div class="timeline-scale__tracks">
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

const totalHours = computed(() => props.endHour - props.startHour)

const hours = computed(() => {
  const list = []
  for (let h = props.startHour; h <= props.endHour; h++) {
    list.push(h)
  }
  return list
})

const getPosition = (timeString) => {
  if (!timeString) return 0

  const parts = timeString.split(':')
  const hoursNum = parseInt(parts[0], 10) || 0
  const minutesNum = parseInt(parts[1], 10) || 0

  const startMinutes = props.startHour * 60
  const endMinutes = props.endHour * 60
  const totalMinutes = endMinutes - startMinutes

  const currentMinutes = hoursNum * 60 + minutesNum
  const offset = currentMinutes - startMinutes

  const percentage = (offset / totalMinutes) * 100
  return Math.min(Math.max(percentage, 0), 100)
}
</script>

<style lang="scss" scoped>
.timeline-scale {
  display: flex;
  flex-direction: column;
  width: 100%;
  user-select: none;

  &__header {
    position: relative;
    height: 1.25rem;
    margin-bottom: 0.35rem;
    width: 100%;
  }

  &__hour-tick {
    position: absolute;
    transform: translateX(-50%);
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--color--font-secondary, #64748b);
    white-space: nowrap;

    &:first-child { transform: translateX(0); }
    &:last-child { transform: translateX(-100%); }
  }

  &__content {
    position: relative;
    width: 100%;
  }

  &__grid {
    position: absolute;
    inset: 0;
    pointer-events: none;
    z-index: 0;
  }

  &__grid-line {
    position: absolute;
    top: 0;
    bottom: 0;
    width: 1px;
    background: var(--color--content-border, #e2e8f0);
    opacity: 0.6;
  }

  &__tracks {
    position: relative;
    z-index: 1;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
  }
}
</style>