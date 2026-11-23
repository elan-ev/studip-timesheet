<template>
    <section v-if="sheet" class="sheet-detail">
        <header class="sheet-detail__header">
            <div>
                <p class="sheet-detail__eyebrow">
                    {{ gettext.$gettext('Zeiterfassungsbogen') }}
                </p>
                <h2 class="sheet-detail__title">
                    {{ monthTitle }}
                </h2>
            </div>

            <div class="sheet-detail__summary">
                <span class="sheet-detail__summary-item">
                    {{ gettext.$gettext('Gesamt erfasst') }}:
                    <strong>{{ formatMinutesToHours(totalWorkedMinutes) }}</strong>
                </span>
            </div>
        </header>

        <table class="default sheet-detail__table">
            <thead>
                <tr>
                    <th>{{ gettext.$gettext('Datum') }}</th>
                    <th>{{ gettext.$gettext('Tag') }}</th>
                    <th>{{ gettext.$gettext('Beginn') }}</th>
                    <th>{{ gettext.$gettext('Ende') }}</th>
                    <th>{{ gettext.$gettext('Pause') }}</th>
                    <th>{{ gettext.$gettext('Dauer') }}</th>
                    <th>{{ gettext.$gettext('Abwesenheit / Feiertag') }}</th>
                    <th>{{ gettext.$gettext('Bemerkung') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-for="day in calendarDays"
                    :key="day.isoDate"
                    :class="{
                        'sheet-detail__row--weekend': day.isWeekend,
                        'sheet-detail__row--holiday': Boolean(day.holidayName),
                    }"
                >
                    <td>{{ day.displayDate }}</td>
                    <td>{{ day.weekdayShort }}</td>
                    <td>{{ formatTime(day.entry?.['start-time']) }}</td>
                    <td>{{ formatTime(day.entry?.['end-time']) }}</td>
                    <td>{{ formatBreak(day.entry) }}</td>
                    <td>{{ day.workedMinutes > 0 ? formatMinutesToHours(day.workedMinutes) : '–' }}</td>
                    <td>
                        <span v-if="day.holidayName" class="sheet-detail__tag sheet-detail__tag--holiday">
                            {{ day.holidayName }}
                        </span>
                        <span v-else-if="day.entry?.['absence-type']" class="sheet-detail__tag">
                            {{ formatAbsenceType(day.entry['absence-type']) }}
                        </span>
                        <span v-else>–</span>
                    </td>
                    <td>{{ day.entry?.comment || '–' }}</td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="5">{{ gettext.$gettext('Summe') }}</th>
                    <th>{{ formatMinutesToHours(totalWorkedMinutes) }}</th>
                    <th colspan="2" />
                </tr>
            </tfoot>
        </table>
    </section>

    <p v-else-if="sheetStore.isLoading" class="sheet-detail__status">
        {{ gettext.$gettext('Stundennachweis wird geladen…') }}
    </p>

    <p v-else class="sheet-detail__status">
        {{ gettext.$gettext('Stundennachweis nicht gefunden.') }}
    </p>
</template>

<script setup>
import { computed, onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import { gettext } from '@/i18n.js';
import { useSheetStore } from '@/store/sheet.js';
import { useRecordStore } from '@/store/record.js';
import { useHolidays } from '@/composables/useHolidays.js';

const route = useRoute();
const sheetStore = useSheetStore();
const recordStore = useRecordStore();
const { getHolidaysForYear } = useHolidays();

const sheetId = computed(() => String(route.params.id ?? ''));
const sheet = computed(() => (sheetId.value ? sheetStore.byId(sheetId.value) : undefined));
const sheetSource = computed(() => sheet.value?.attributes ?? sheet.value ?? {});

const sheetYear = computed(() => Number(sheetSource.value.year ?? new Date().getFullYear()));
const sheetMonth = computed(() => Number(sheetSource.value.month ?? new Date().getMonth() + 1));

const monthTitle = computed(() => {
    const date = new Date(Date.UTC(sheetYear.value, sheetMonth.value - 1, 1));
    const monthName = new Intl.DateTimeFormat('de-DE', { month: 'long', timeZone: 'UTC' }).format(date);
    return `${monthName} ${sheetYear.value}`;
});

const rawRecords = computed(() => {
    if (!sheetId.value) return [];
    if (typeof recordStore.bySheetId === 'function') {
        return recordStore.bySheetId(sheetId.value) ?? [];
    }
    const embedded = sheetSource.value?.records?.data ?? sheetSource.value?.records ?? [];
    return Array.isArray(embedded) ? embedded : [];
});

const recordsByDate = computed(() => {
    const map = new Map();
    for (const item of rawRecords.value) {
        const attrs = item?.attributes ?? item;
        if (attrs?.date) {
            map.set(String(attrs.date), attrs);
        }
    }
    return map;
});

function parseTimeToMinutes(timeStr) {
    if (!timeStr) return null;
    const parts = String(timeStr).split(':').map(Number);
    if (parts.length < 2 || Number.isNaN(parts[0]) || Number.isNaN(parts[1])) return null;
    return parts[0] * 60 + parts[1];
}

function calculateWorkedMinutes(entry) {
    if (!entry) return 0;
    const startMin = parseTimeToMinutes(entry['start-time']);
    const endMin = parseTimeToMinutes(entry['end-time']);
    if (startMin === null || endMin === null || endMin <= startMin) return 0;
    const breakMin = Number(entry['break-duration'] ?? 0);
    return Math.max(0, endMin - startMin - breakMin);
}

const calendarDays = computed(() => {
    const year = sheetYear.value;
    const month = sheetMonth.value;
    if (!year || !month) return [];

    const holidays = getHolidaysForYear(year);
    const daysInMonth = new Date(Date.UTC(year, month, 0)).getUTCDate();
    const weekdayFormatter = new Intl.DateTimeFormat('de-DE', { weekday: 'short', timeZone: 'UTC' });
    const days = [];

    for (let day = 1; day <= daysInMonth; day += 1) {
        const dateObj = new Date(Date.UTC(year, month - 1, day));
        const dayStr = String(day).padStart(2, '0');
        const monthStr = String(month).padStart(2, '0');
        const isoDate = `${year}-${monthStr}-${dayStr}`;
        const weekdayIndex = dateObj.getUTCDay();
        const entry = recordsByDate.value.get(isoDate) ?? null;

        days.push({
            isoDate,
            displayDate: `${dayStr}.${monthStr}.${year}`,
            weekdayShort: weekdayFormatter.format(dateObj),
            isWeekend: weekdayIndex === 0 || weekdayIndex === 6,
            holidayName: holidays.get(isoDate) ?? '',
            entry,
            workedMinutes: calculateWorkedMinutes(entry),
        });
    }

    return days;
});

const totalWorkedMinutes = computed(() => {
    return calendarDays.value.reduce((sum, day) => sum + day.workedMinutes, 0);
});

function formatTime(timeStr) {
    if (!timeStr) return '–';
    return String(timeStr).slice(0, 5);
}

function formatBreak(entry) {
    if (!entry) return '–';
    const duration = Number(entry['break-duration'] ?? 0);
    if (!duration && !entry['break-start']) return '–';
    const start = entry['break-start'] ? `${formatTime(entry['break-start'])} ` : '';
    return duration > 0 ? `${start}(${duration} Min.)` : start.trim();
}

function formatMinutesToHours(totalMinutes) {
    const minutes = Math.max(0, Math.round(Number(totalMinutes ?? 0)));
    const hrs = Math.floor(minutes / 60);
    const mins = String(minutes % 60).padStart(2, '0');
    return `${hrs}:${mins} ${gettext.$gettext('Std.')}`;
}

function formatAbsenceType(type) {
    const labels = {
        vacation: gettext.$gettext('Urlaub'),
        sick: gettext.$gettext('Krankheit'),
        illness: gettext.$gettext('Krankheit'),
        holiday: gettext.$gettext('Feiertag'),
        compensation: gettext.$gettext('Freizeitausgleich'),
    };
    return labels[type] ?? String(type);
}

async function ensureSheetLoaded(id) {
    if (!id) return;
    await sheetStore.fetchById(id, ['records']);
}

onMounted(() => {
    ensureSheetLoaded(sheetId.value);
});

watch(sheetId, (newId) => {
    ensureSheetLoaded(newId);
});
</script>

<style lang="scss" scoped>
.sheet-detail {
    display: flex;
    flex-direction: column;
    gap: 1rem;

    &__header {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: flex-end;
        gap: 1rem;
    }

    &__eyebrow {
        margin: 0 0 0.25rem;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--color--font-secondary, #666666);
    }

    &__title {
        margin: 0;
        font-size: 1.35rem;
        color: var(--color--font-primary, #333333);
    }

    &__summary {
        display: flex;
        gap: 1.5rem;
        font-size: 0.95rem;
        color: var(--color--font-primary, #333333);
    }

    &__row--weekend {
        background-color: var(--color--content-bg-light, #f6f7f9);
        color: var(--color--font-secondary, #666666);
    }

    &__row--holiday {
        background-color: var(--color--info-bg, #eef6fc);
    }

    &__tag {
        display: inline-block;
        padding: 0.1rem 0.5rem;
        border-radius: 999px;
        font-size: 0.8rem;
        font-weight: 600;
        background: var(--color--content-bg-light, #e9edf3);
        color: var(--color--font-secondary, #444444);

        &--holiday {
            background: var(--color--info-bg, #dbeafe);
            color: var(--color--info-text, #1e40af);
        }
    }

    &__status {
        margin: 0;
        padding: 1.25rem;
        background: var(--color--content-bg-light, #fafbfd);
        color: var(--color--font-secondary, #666666);
    }
}
</style>