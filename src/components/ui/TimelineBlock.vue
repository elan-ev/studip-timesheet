<template>
  <div class="timeline-record-group" @click="$emit('click', record)">
    <template v-if="record['absence-type'] && record['absence-type'] !== 'work'">
      <div 
        class="timeline-block timeline-block--absence timeline-block--rounded-full"
        :style="absenceStyle"
        :title="`${record['absence-type']}: ${record['start-time']} - ${record['end-time']}`"
      >
        <span class="timeline-block__label">{{ record['absence-type'] }}</span>
      </div>
    </template>

    <template v-else>
      <div 
        class="timeline-block timeline-block--work"
        :class="hasBreak ? 'timeline-block--rounded-start' : 'timeline-block--rounded-full'"
        :style="workBlock1Style"
        :title="`Arbeit: ${record['start-time']} - ${hasBreak ? record['break-start'] : record['end-time']}`"
      >
        <span class="timeline-block__label">{{ record.comment || 'Arbeit' }}</span>
      </div>

      <div 
        v-if="hasBreak"
        class="timeline-block timeline-block--pause timeline-block--rounded-none"
        :style="pauseBlockStyle"
        :title="`Pause (${record['break-duration']} Min): ${record['break-start']} - ${breakEndTime}`"
      >
        <span class="timeline-block__label">Pause</span>
      </div>

      <div 
        v-if="hasBreak"
        class="timeline-block timeline-block--work timeline-block--rounded-end"
        :style="workBlock2Style"
        :title="`Arbeit: ${breakEndTime} - ${record['end-time']}`"
      >
        <span class="timeline-block__label"></span>
      </div>
    </template>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  record: {
    type: Object,
    required: true,
  },
  getPosition: {
    type: Function,
    required: true,
  },
})

defineEmits(['click'])

const addMinutesToTime = (timeStr, minutes) => {
  if (!timeStr) return ''
  const parts = timeStr.split(':')
  const h = parseInt(parts[0], 10) || 0
  const m = parseInt(parts[1], 10) || 0
  
  const totalMin = h * 60 + m + minutes
  const newH = Math.floor(totalMin / 60) % 24
  const newM = totalMin % 60
  
  return `${String(newH).padStart(2, '0')}:${String(newM).padStart(2, '0')}:00`
}

const hasBreak = computed(() => {
  return Boolean(props.record['break-start'] && props.record['break-duration'] > 0)
})

const breakEndTime = computed(() => {
  if (!hasBreak.value) return ''
  return addMinutesToTime(props.record['break-start'], props.record['break-duration'])
})

const customColor = computed(() => {
  return props.record.color || props.record['contract-color'] || null
})

const createWorkStyle = (left, right) => {
  const style = {
    left: `${left}%`,
    width: `${Math.max(right - left, 0)}%`,
  }
  if (customColor.value) {
    style.backgroundColor = customColor.value
  }
  return style
}

const absenceStyle = computed(() => {
  const left = props.getPosition(props.record['start-time'])
  const right = props.getPosition(props.record['end-time'])
  return { left: `${left}%`, width: `${Math.max(right - left, 0)}%` }
})

const workBlock1Style = computed(() => {
  const left = props.getPosition(props.record['start-time'])
  const endPoint = hasBreak.value ? props.record['break-start'] : props.record['end-time']
  const right = props.getPosition(endPoint)
  return createWorkStyle(left, right)
})

const pauseBlockStyle = computed(() => {
  if (!hasBreak.value) return {}
  const left = props.getPosition(props.record['break-start'])
  const right = props.getPosition(breakEndTime.value)
  return { left: `${left}%`, width: `${Math.max(right - left, 0)}%` }
})

const workBlock2Style = computed(() => {
  if (!hasBreak.value) return {}
  const left = props.getPosition(breakEndTime.value)
  const right = props.getPosition(props.record['end-time'])
  return createWorkStyle(left, right)
})
</script>

<style lang="scss" scoped>
.timeline-record-group {
  position: absolute;
  inset: 0;
}

.timeline-block {
  position: absolute;
  top: 4px;
  bottom: 4px;
  border-radius: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 0.5rem;
  box-sizing: border-box;
  font-size: 0.75rem;
  font-weight: 600;
  white-space: nowrap;
  overflow: hidden;

  &--rounded-full {
    border-radius: 4px;
  }

  &--rounded-start {
    border-radius: 4px 0 0 4px;
  }

  &--rounded-end {
    border-radius: 0 4px 4px 0;
  }

  &--rounded-none {
    border-radius: 0;
  }

  &__label {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  &--work {
    background-color: var(--color--primary, #2563eb);
    color: #000;
  }

  &--pause {
    background: repeating-linear-gradient(
      -45deg,
      #e2e8f0,
      #e2e8f0 6px,
      #cbd5e1 6px,
      #cbd5e1 12px
    );
    border-left: none;
    border-right: none;
    color: var(--color--font-secondary, #475569);
  }

  &--absence {
    background: var(--color--warning-10, #fef3c7);
    border: 1px solid var(--color--warning-40, #f59e0b);
    color: var(--color--warning-100, #92400e);
  }
}
</style>