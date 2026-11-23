<template>
    <template v-if="contract">
        <article class="contract-card">
            <header class="contract-card__header">
                <div class="contract-card__title-group">
                    <p class="contract-card__eyebrow">
                        {{ contractType || gettext.$gettext('Vertrag') }}
                    </p>
                    <h2 class="contract-card__title">
                        {{ contractLabel || gettext.$gettext('Unbenannter Vertrag') }}
                    </h2>
                    <p class="contract-card__employee">
                        {{ employeeName }}
                    </p>
                </div>

                <div class="contract-card__kpi">
                    <span class="contract-card__kpi-value">{{ hoursLabel }}</span>
                    <span class="contract-card__kpi-meta">{{ periodLabel }}</span>
                </div>
            </header>

            <dl class="contract-card__details">
                <div class="contract-card__detail">
                    <dt>{{ gettext.$gettext('Beschäftigte Person') }}</dt>
                    <dd>
                        {{ employeeName }}
                        <span class="contract-card__id">(#{{ employeeId }})</span>
                    </dd>
                </div>

                <div class="contract-card__detail">
                    <dt>{{ gettext.$gettext('Einrichtung') }}</dt>
                    <dd>{{ instituteId }}</dd>
                </div>

                <div class="contract-card__detail">
                    <dt>{{ gettext.$gettext('Vertragszeitraum') }}</dt>
                    <dd>{{ periodLabel }}</dd>
                </div>

                <div class="contract-card__detail">
                    <dt>{{ gettext.$gettext('Monatskontingent') }}</dt>
                    <dd>{{ hoursLabel }}</dd>
                </div>

                <div class="contract-card__detail">
                    <dt>{{ gettext.$gettext('Erster Monat') }}</dt>
                    <dd>
                        <span
                            class="contract-card__badge"
                            :class="{ 'contract-card__badge--active': halfHoursFirstMonth }"
                        >
                            {{
                                halfHoursFirstMonth
                                    ? gettext.$gettext('Halbe Stunden aktiv')
                                    : gettext.$gettext('Regulär')
                            }}
                        </span>
                    </dd>
                </div>

                <div class="contract-card__detail">
                    <dt>{{ gettext.$gettext('Letzter Monat') }}</dt>
                    <dd>
                        <span
                            class="contract-card__badge"
                            :class="{ 'contract-card__badge--active': halfHoursLastMonth }"
                        >
                            {{
                                halfHoursLastMonth
                                    ? gettext.$gettext('Halbe Stunden aktiv')
                                    : gettext.$gettext('Regulär')
                            }}
                        </span>
                    </dd>
                </div>
            </dl>
        </article>

        <section class="contract-sheets">
            <h3 class="contract-sheets__heading">
                {{ gettext.$gettext('Stundennachweise') }}
            </h3>

            <p v-if="sheetStore.isLoading && sheets.length === 0" class="contract-card__status">
                {{ gettext.$gettext('Stundennachweise werden geladen…') }}
            </p>

            <table v-else-if="sheets.length > 0" class="default">
                <thead>
                    <tr>
                        <th>{{ gettext.$gettext('Zeitraum') }}</th>
                        <th>{{ gettext.$gettext('Soll-Stunden') }}</th>
                        <th>{{ gettext.$gettext('Ist-Stunden') }}</th>
                        <th>{{ gettext.$gettext('Übertrag') }}</th>
                        <th>{{ gettext.$gettext('Status') }}</th>
                        <th class="actions">{{ gettext.$gettext('Aktionen') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="sheet in sheets" :key="sheet.id">
                        <td>
                            <RouterLink :to="getSheetRoute(sheet.id)">
                                {{ formatSheetPeriod(sheet) }}
                            </RouterLink>
                        </td>
                        <td>{{ formatHours(getSheetAttr(sheet, 'target-hours') ?? getSheetAttr(sheet, 'hours')) }}</td>
                        <td>{{ formatHours(getSheetAttr(sheet, 'actual-hours') ?? getSheetAttr(sheet, 'worked-hours')) }}</td>
                        <td>{{ formatHours(getSheetAttr(sheet, 'carryover-hours') ?? getSheetAttr(sheet, 'carryover')) }}</td>
                        <td>{{ formatSheetStatus(sheet) }}</td>
                        <td class="actions">
                            <RouterLink :to="getSheetRoute(sheet.id)">
                                {{ gettext.$gettext('Öffnen') }}
                            </RouterLink>
                        </td>
                    </tr>
                </tbody>
            </table>

            <p v-else class="contract-card__status">
                {{ gettext.$gettext('Für diesen Vertrag liegen noch keine Stundennachweise vor.') }}
            </p>
        </section>
    </template>

    <p v-else-if="contractStore.isLoading" class="contract-card__status">
        {{ gettext.$gettext('Vertragsdaten werden geladen…') }}
    </p>

    <p v-else class="contract-card__status">
        {{ gettext.$gettext('Vertrag nicht gefunden.') }}
    </p>
</template>

<script setup>
import { computed, onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import { gettext } from '@/i18n.js';
import { useContractStore } from '@/store/contract.js';
import { useSheetStore } from '@/store/sheet.js';

const route = useRoute();
const contractStore = useContractStore();
const sheetStore = useSheetStore();

const contractId = computed(() => String(route.params.id ?? ''));
const contract = computed(() => (contractId.value ? contractStore.byId(contractId.value) : undefined));
const sheets = computed(() => (contractId.value ? sheetStore.byContractId(contractId.value) : []));

const source = computed(() => contract.value?.attributes ?? contract.value ?? {});

const instituteId = computed(() => source.value['institute-id'] ?? '');
const employeeId = computed(() => source.value['employee-id'] ?? '');
const employeeName = computed(() => source.value['employee-name'] ?? '');
const contractType = computed(() => source.value['contract-type'] ?? '');
const contractLabel = computed(() => source.value.label ?? '');
const halfHoursFirstMonth = computed(() => Boolean(source.value['half-hours-first-month']));
const halfHoursLastMonth = computed(() => Boolean(source.value['half-hours-last-month']));

const periodLabel = computed(() => {
    const start = source.value['start-date'] || gettext.$gettext('Kein Startdatum');
    const end = source.value['end-date'] || gettext.$gettext('Unbefristet');
    return `${start} – ${end}`;
});

const hoursLabel = computed(() => {
    const hours = Number(source.value['hours-per-month'] ?? 0);
    return `${hours} ${gettext.$gettext('Std. / Monat')}`;
});

function getSheetAttr(sheet, key) {
    return sheet?.attributes?.[key] ?? sheet?.[key];
}

function formatSheetPeriod(sheet) {
    const label = getSheetAttr(sheet, 'label');
    if (label) return label;

    const year = getSheetAttr(sheet, 'year');
    const month = getSheetAttr(sheet, 'month');
    if (year && month) {
        return `${String(month).padStart(2, '0')}/${year}`;
    }

    return `${gettext.$gettext('Stundennachweis')} #${sheet.id}`;
}

function formatHours(value) {
    if (value === undefined || value === null || value === '') {
        return '–';
    }
    return `${Number(value)} ${gettext.$gettext('Std.')}`;
}

function formatSheetStatus(sheet) {
    const status = getSheetAttr(sheet, 'status');
    if (status) return status;

    const isClosed = getSheetAttr(sheet, 'closed') ?? getSheetAttr(sheet, 'is-closed');
    if (typeof isClosed === 'boolean') {
        return isClosed ? gettext.$gettext('Abgeschlossen') : gettext.$gettext('Offen');
    }

    return '–';
}

function getSheetRoute(sheetId) {
    return {
        name: 'admin-sheet-detail',
        params: { id: String(sheetId) },
        query: contractId.value ? { contractId: contractId.value } : undefined,
    };
}

async function ensureData(id) {
    if (!id) return;
    if (!contractStore.byId(id)) {
        await contractStore.fetchById(id);
    }
    await sheetStore.fetchByContractId(id);
}

onMounted(() => {
    ensureData(contractId.value);
});

watch(contractId, (newId) => {
    ensureData(newId);
});
</script>

<style lang="scss" scoped>
.contract-card {
    background: var(--surface-card, #ffffff);
    border: 1px solid var(--border-subtle, #d8dee6);
    border-radius: var(--radius-lg, 12px);
    padding: 1.5rem;
    box-shadow: var(--shadow-sm, 0 2px 8px rgba(15, 23, 42, 0.05));

    &__header {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem 2rem;
        padding-bottom: 1.25rem;
        margin-bottom: 1.25rem;
        border-bottom: 1px solid var(--border-subtle, #e5e9f0);
    }

    &__eyebrow {
        margin: 0 0 0.25rem;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--text-muted, #5c6675);
    }

    &__title {
        margin: 0;
        font-size: 1.35rem;
        line-height: 1.25;
        color: var(--text-primary, #18202c);
    }

    &__employee {
        margin: 0.35rem 0 0;
        font-size: 0.95rem;
        color: var(--text-secondary, #3d4756);
    }

    &__kpi {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        background: var(--surface-accent, #f4f7fb);
        padding: 0.75rem 1rem;
        border-radius: var(--radius-md, 8px);
    }

    &__kpi-value {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text-primary, #18202c);
    }

    &__kpi-meta {
        font-size: 0.85rem;
        color: var(--text-muted, #5c6675);
        margin-top: 0.2rem;
    }

    &__details {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1rem 1.5rem;
        margin: 0;
    }

    &__detail {
        background: var(--surface-subtle, #fafbfd);
        border: 1px solid var(--border-faint, #edf0f5);
        border-radius: var(--radius-md, 8px);
        padding: 0.85rem 1rem;

        dt {
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--text-muted, #5c6675);
            margin-bottom: 0.35rem;
        }

        dd {
            margin: 0;
            font-size: 0.95rem;
            font-weight: 500;
            color: var(--text-primary, #18202c);
            word-break: break-word;
        }
    }

    &__id {
        font-size: 0.82rem;
        color: var(--text-muted, #5c6675);
        font-weight: 400;
    }

    &__badge {
        display: inline-flex;
        align-items: center;
        padding: 0.2rem 0.6rem;
        border-radius: 999px;
        font-size: 0.8rem;
        font-weight: 600;
        background: var(--surface-neutral-pill, #e9edf3);
        color: var(--text-secondary, #3d4756);

        &--active {
            background: var(--surface-info-pill, #e0f2fe);
            color: var(--text-info, #075985);
        }
    }

    &__status {
        margin: 0;
        padding: 1.25rem;
        border-radius: var(--radius-md, 8px);
        background: var(--surface-subtle, #fafbfd);
        color: var(--text-muted, #5c6675);
    }
}

.contract-sheets {
    margin-top: 1.5rem;

    &__heading {
        margin: 0 0 0.75rem;
        font-size: 1.15rem;
        color: var(--text-primary, #18202c);
    }
}
</style>