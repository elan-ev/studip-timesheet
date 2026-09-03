<template>
  <div class="worker-overview-timeline">
    <section class="worker-overview-timeline__day">
      <header class="worker-overview-timeline__header">
        <div class="header-top-row">
          <div class="header-title-group">
            <h2 class="header-title">{{ selectedDay.title }}</h2>
            <span class="header-date">{{ selectedDay.formattedDate }}</span>
          </div>

          <div class="header-actions-group">
            <div class="header-checks">
              <span 
                class="check-chip"
                :class="dayStats.pauseCompliant ? 'check-chip--success' : 'check-chip--warning'"
              >
                <span class="check-chip__icon">{{ dayStats.pauseCompliant ? '✓' : '⚠' }}</span>
                Pflichtpause ({{ dayStats.requiredPauseTime }})
              </span>

              <span 
                class="check-chip"
                :class="dayStats.maxHoursCompliant ? 'check-chip--success' : 'check-chip--danger'"
              >
                <span class="check-chip__icon">{{ dayStats.maxHoursCompliant ? '✓' : '✕' }}</span>
                Max. 10 Std.
              </span>

              <span 
                class="check-chip"
                :class="dayStats.nightWorkCompliant ? 'check-chip--success' : 'check-chip--warning'"
              >
                <span class="check-chip__icon">{{ dayStats.nightWorkCompliant ? '✓' : '⚠' }}</span>
                Keine Nachtarbeit
              </span>
            </div>
          </div>
        </div>

        <div class="header-stats">
          <div class="stat-item">
            <span class="stat-item__label">Arbeitszeit:</span>
            <strong class="stat-item__value stat-item__value--highlight">{{ dayStats.totalWorkTime }}</strong>
          </div>
          <span class="stat-separator">•</span>
          <div class="stat-item">
            <span class="stat-item__label">Pause:</span>
            <span class="stat-item__value">{{ dayStats.totalPauseTime }}</span>
          </div>
          <span class="stat-separator">•</span>
          <div class="stat-item">
            <span class="stat-item__label">Fenster:</span>
            <span class="stat-item__value">{{ dayStats.firstStamp }} – {{ dayStats.lastStamp }} Uhr</span>
          </div>
        </div>
      </header>

      <div class="worker-overview-timeline__body">
        <TimelineScale :start-hour="6" :end-hour="22" v-slot="{ getPosition }">
          <TimelineTrack 
            v-for="(track, tIndex) in processedTracks" 
            :key="tIndex"
          >
            <TimelineBlock
              v-for="(record, rIndex) in track"
              :key="rIndex"
              :record="record"
              :get-position="getPosition"
              @click="handleRecordClick"
            />
          </TimelineTrack>
        </TimelineScale>
      </div>

      <footer class="worker-overview-timeline__footer">
        <div class="footer-items">
          <div 
            v-for="(data, contract) in dayStats.contractBreakdown" 
            :key="contract"
            class="footer-item"
          >
            <span class="footer-item__dot" :style="{ backgroundColor: data.color }"></span>
            <span class="footer-item__name">{{ contract }}</span>
            <strong class="footer-item__hours">{{ data.hours }}</strong>
          </div>
        </div>
      </footer>
    </section>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import TimelineScale from '../ui/TimelineScale.vue'
import TimelineTrack from '../ui/TimelineTrack.vue'
import TimelineBlock from '../ui/TimelineBlock.vue'

const props = defineProps({
  dayData: {
    type: Object,
    default: undefined,
  },
})

const emit = defineEmits(['record-click'])

const contractColors = {
  'Tutorium Informatik A': '#d0ebea',
  'Tutorium Datenbanksysteme': '#e1d5e8',
}

const statusLabels = {
  draft: 'Entwurf',
  submitted: 'Eingereicht',
  approved: 'Freigegeben',
}

const selectedDay = computed(() => {
  return props.dayData || {
    date: '2026-09-03',
    title: 'Donnerstag',
    formattedDate: '03. September 2026',
    status: 'draft',
    records: [
      {
        id: 1,
        date: '2026-09-03',
        'start-time': '08:00:00',
        'end-time': '16:30:00',
        'break-start': '12:00:00',
        'break-duration': 45,
        'absence-type': null,
        comment: 'Info A - Gruppe 1 - 3',
        contract: 'Tutorium Informatik A',
        color: contractColors['Tutorium Informatik A'],
      },
      {
        id: 2,
        date: '2026-09-03',
        'start-time': '17:00:00',
        'end-time': '19:00:00',
        'break-start': null,
        'break-duration': 0,
        'absence-type': null,
        comment: 'Gruppe 1 - 3',
        contract: 'Tutorium Datenbanksysteme',
        color: contractColors['Tutorium Datenbanksysteme'],
      },
    ],
  }
})

const processedTracks = computed(() => {
  const sortedRecords = [...selectedDay.value.records].sort((a, b) => {
    return a['start-time'].localeCompare(b['start-time'])
  })

  const tracks = []

  sortedRecords.forEach((record) => {
    let placed = false

    for (const track of tracks) {
      const lastRecord = track[track.length - 1]
      if (lastRecord['end-time'] <= record['start-time']) {
        track.push(record)
        placed = true
        break
      }
    }

    if (!placed) {
      tracks.push([record])
    }
  })

  return tracks
})

const dayStats = computed(() => {
  return {
    firstStamp: '08:00',
    lastStamp: '19:00',
    totalWorkTime: '7h 45m',
    totalPauseTime: '45m',
    requiredPauseTime: '30m',
    pauseCompliant: true,
    maxHoursCompliant: true,
    nightWorkCompliant: true,
    contractBreakdown: {
      'Tutorium Informatik A': { hours: '5h 45m', color: contractColors['Tutorium Informatik A'] },
      'Tutorium Datenbanksysteme': { hours: '2h 00m', color: contractColors['Tutorium Datenbanksysteme'] },
    },
  }
})

const handleRecordClick = (record) => {
  emit('record-click', record)
}
</script>

<style lang="scss" scoped>
.worker-overview-timeline {
  display: flex;
  flex-direction: column;

  &__day {
    height: 100%;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    justify-content: space-between;
  }

  &__header {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    padding-bottom: 1rem;
  }

  &__body {
    padding: 2.5rem 0;
    border-top: solid thin #e2e8f0;
    border-bottom: solid thin #e2e8f0;
  }

  &__footer {
    display: flex;
    justify-content: flex-end;
    padding-top: 0.75rem;
  }
}

.header-top-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  flex-wrap: wrap;
}

.header-title-group {
  display: flex;
  align-items: baseline;
  gap: 0.75rem;
}

.header-title {
  font-size: 1.6rem;
  font-weight: 800;
  margin: 0;
  color: var(--color--font-primary, #0f172a);
}

.header-date {
  font-size: 0.95rem;
  color: var(--color--font-secondary, #64748b);
}

.header-actions-group {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.header-checks {
  display: flex;
  align-items: center;
  gap: 0.4rem;
}

.check-chip {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  font-size: 0.75rem;
  font-weight: 600;
  padding: 0.25rem 0.6rem;
  border-radius: 6px;
  border: 1px solid transparent;

  &__icon {
    font-size: 0.7rem;
  }

  &--success {
    background: #f0fdf4;
    color: #166534;
    border-color: #bbf7d0;
  }

  &--warning {
    background: #fffbeb;
    color: #92400e;
    border-color: #fde68a;
  }

  &--danger {
    background: #fef2f2;
    color: #991b1b;
    border-color: #fecaca;
  }
}

.header-stats {
  display: flex;
  align-items: center;
  gap: 1.25rem;
  font-size: 0.95rem;
  padding-top: 0.5rem;
}

.stat-item {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  color: #64748b;

  &__label {
  }

  &__value {
    font-weight: 700;
  }
}

.stat-separator {
  color: var(--color--content-border, #cbd5e1);
  font-size: 0.75rem;
}

.footer-items {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 0.35rem;
}

.footer-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.85rem;
  color: #64748b;

  &__dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
  }

  &__name {
  }

  &__hours {
    font-weight: 700;
  }
}
</style>