<template>
  <div class="worker-overview-calendar">
    <header class="worker-overview-calendar__header">
      <h2>{{ currentMonthLabel }}</h2>
    </header>

    <!-- Wochentags-Header -->
    <div class="worker-overview-calendar__weekdays">
      <div v-for="day in weekDays" :key="day" class="worker-overview-calendar__weekday">
        {{ day }}
      </div>
    </div>

    <!-- Kalender-Raster -->
    <div class="worker-overview-calendar__grid">
      <div
        v-for="(day, index) in calendarDays"
        :key="index"
        class="worker-overview-calendar__day"
        :class="{
          'worker-overview-calendar__day--outside': !day.isCurrentMonth,
          'worker-overview-calendar__day--today': day.isToday,
          'worker-overview-calendar__day--has-entries': day.contracts.length > 0
        }"
      >
        <span class="worker-overview-calendar__day-number">{{ day.dayNumber }}</span>

        <!-- Vertrags-Badges & Arbeitszeiten -->
        <div v-if="day.contracts.length > 0" class="worker-overview-calendar__day-content">
          <div
            v-for="contract in day.contracts"
            :key="contract.id"
            class="worker-overview-calendar__contract-tag"
            :style="{ '--contract-color': contract.color }"
            :title="`${contract.name}: ${contract.totalHours} Std.`"
          >
            <span class="worker-overview-calendar__contract-indicator" />
            <span class="worker-overview-calendar__contract-hours">{{ contract.totalHours }}h</span>
          </div>

        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
  year: {
    type: Number,
    default: () => new Date().getFullYear(),
  },
  month: {
    type: Number,
    default: () => new Date().getMonth(), // 0-basiert (0 = Jan, 7 = Aug, etc.)
  },
})

const weekDays = ['Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa', 'So']

// Verträge mit festen Farben zur Unterscheidung
const CONTRACT_META = {
  info_a: { name: 'Tutorium Informatik A', color: '#d0ebea' },
  dbs: { name: 'Tutorium Datenbanksysteme', color: '#e1d5e8' },
}

// Generiert dynamisch Dummydaten verteilt über den aktuellen Monat
const dummyMonthData = computed(() => {
  const data = {}
  const now = new Date()
  const y = props.year
  const m = props.month

  const formatDateKey = (day) => {
    const d = new Date(y, m, day)
    return d.toISOString().split('T')[0]
  }

  // Einige Beispiel-Arbeitstage im laufenden Monat setzen
  data[formatDateKey(4)] = [
    { contractId: 'info_a', hours: 4.25 },
  ]
  data[formatDateKey(5)] = [
    { contractId: 'dbs', hours: 2.5 },
  ]
  data[formatDateKey(11)] = [
    { contractId: 'info_a', hours: 4.0 },
    { contractId: 'dbs', hours: 1.5 },
  ]
  data[formatDateKey(12)] = [
    { contractId: 'info_a', hours: 3.5 },
  ]
  data[formatDateKey(18)] = [
    { contractId: 'dbs', hours: 5.0 },
  ]
  data[formatDateKey(now.getDate())] = [
    { contractId: 'info_a', hours: 4.75 },
    { contractId: 'dbs', hours: 1.25 },
  ]

  return data
})

const currentMonthLabel = computed(() => {
  const date = new Date(props.year, props.month, 1)
  return date.toLocaleDateString('de-DE', { month: 'long', year: 'numeric' })
})

// Baut das komplette Grid inklusive Vor- und Folgemonat-Tagen auf
const calendarDays = computed(() => {
  const year = props.year
  const month = props.month

  const firstDayOfMonth = new Date(year, month, 1)
  const lastDayOfMonth = new Date(year, month + 1, 0)

  // Wochentag-Offset für Montag als Startpunkt (0 = Mo, 6 = So)
  let startOffset = firstDayOfMonth.getDay() - 1
  if (startOffset === -1) startOffset = 6

  const days = []
  const todayStr = new Date().toISOString().split('T')[0]

  // Tage aus dem Vormonat auffüllen
  const prevMonthLastDay = new Date(year, month, 0).getDate()
  for (let i = startOffset - 1; i >= 0; i--) {
    days.push({
      dateKey: null,
      dayNumber: prevMonthLastDay - i,
      isCurrentMonth: false,
      isToday: false,
      contracts: [],
      totalHours: 0,
    })
  }

  // Tage des aktuellen Monats
  for (let day = 1; day <= lastDayOfMonth.getDate(); day++) {
    const currentDate = new Date(year, month, day)
    // Manuelles Datums-Formatting zur Vermeidung von UTC-Offset-Sprüngen
    const dateKey = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`
    
    const entries = dummyMonthData.value[dateKey] || []
    const contracts = entries.map((entry) => ({
      id: entry.contractId,
      name: CONTRACT_META[entry.contractId]?.name || entry.contractId,
      color: CONTRACT_META[entry.contractId]?.color || '#64748b',
      totalHours: entry.hours,
    }))

    const totalHours = contracts.reduce((sum, c) => sum + c.totalHours, 0)

    days.push({
      dateKey,
      dayNumber: day,
      isCurrentMonth: true,
      isToday: dateKey === todayStr,
      contracts,
      totalHours,
    })
  }

  // Restliche Zellen für volles 7er-Raster auffüllen
  const remaining = 7 - (days.length % 7)
  if (remaining < 7) {
    for (let i = 1; i <= remaining; i++) {
      days.push({
        dateKey: null,
        dayNumber: i,
        isCurrentMonth: false,
        isToday: false,
        contracts: [],
        totalHours: 0,
      })
    }
  }

  return days
})
</script>

<style lang="scss" scoped>
.worker-overview-calendar {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  width: 100%;

  &__header {
    h2 {
      font-size: 1.25rem;
      font-weight: 700;
      margin: 0;
      color: var(--color--font-primary, #1e293b);
    }
  }

  &__weekdays {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 0.5rem;
    text-align: center;
  }

  &__weekday {
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--color--font-secondary, #64748b);
    padding: 0.25rem 0;
  }

  &__grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 0.5rem;
  }

  &__day {
    aspect-ratio: 1 / 1;
    background: var(--color--bg-surface, #ffffff);
    border: 1px solid var(--color--content-border, #e2e8f0);
    border-radius: var(--border-radius--base, 6px);
    padding: 0.5rem;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    position: relative;
    overflow: hidden;

    &--outside {
      background: var(--color--black-5, #f8fafc);
      opacity: 0.4;
    }

    &--today {
      border-color: var(--color--primary, #2563eb);
      border-width: 2px;
    }

    &--has-entries {
      background: var(--color--bg-highlight, #f0f9ff);
    }
  }

  &__day-number {
    font-size: 0.85rem;
    font-weight: 700;
    color: var(--color--font-primary, #1e293b);
  }

  &__day-content {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    overflow: hidden;
  }

  &__contract-tag {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.7rem;
    font-weight: 600;
    line-height: 1;
  }

  &__contract-indicator {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background-color: var(--contract-color, #64748b);
    flex-shrink: 0;
  }

  &__contract-hours {
    color: var(--color--font-secondary, #334155);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  &__day-total {
    font-size: 0.65rem;
    font-weight: 700;
    color: var(--color--font-secondary, #64748b);
    border-top: 1px dashed var(--color--content-border, #cbd5e1);
    padding-top: 0.25rem;
    margin-top: 0.15rem;
  }
}
</style>