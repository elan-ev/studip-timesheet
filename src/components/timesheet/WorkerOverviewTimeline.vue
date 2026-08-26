<template>
  <div class="worker-overview-timeline">
    <h3>Heute</h3>
    
    <TimelineScale :start-hour="6" :end-hour="22" v-slot="{ getPosition }">
      <TimelineBlock
        v-for="(item, index) in processedTimeline"
        :key="index"
        :start="item.start"
        :end="item.end"
        :type="item.type"
        :label="item.label"
        :get-position="getPosition"
      />
    </TimelineScale>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import TimelineScale from '../ui/SuiTimelineScale.vue'
import TimelineBlock from '../ui/SuiTimelineBlock.vue'

const props = defineProps({
  entries: {
    type: Array,
    default: () => [],
  },
})

const dummyEntries = computed(() => {
  const today = new Date()

  const createDate = (hours, minutes = 0) => {
    const date = new Date(today)
    date.setHours(hours, minutes, 0, 0)
    return date
  }

  return [
    {
      start: createDate(8, 0),
      end: createDate(12, 15),
      label: 'Info A - Gruppe 6 - 8',
    },
    {
      start: createDate(13, 0),
      end: createDate(16, 45),
      label: 'Info A - Gruppe 11 - 16',
    },
  ]
})

const processedTimeline = computed(() => {
  const entriesToProcess = dummyEntries.value

  if (!entriesToProcess.length) return []

  const sorted = [...entriesToProcess].sort((a, b) => a.start - b.start)
  const result = []

  for (let i = 0; i < sorted.length; i++) {
    const current = sorted[i]
    const previous = result[result.length - 1]

    if (previous && current.start > previous.end) {
      result.push({
        start: previous.end,
        end: current.start,
        type: 'pause',
        label: 'Pause',
      })
    }

    result.push({
      ...current,
      type: 'work',
    })
  }

  return result
})
</script>