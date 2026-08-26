<template>
    <div class="worker-overview-kpis">
        <SuiCard orientation="horizontal" subtitle="Geleistete Stunden" title="30,5 / 160 h">
            <template #visual>
                <SuiProgressRing :value="30.5" :max="160" :size="48" :strokeWidth="4">
                    <StudipIcon shape="date" :size="20" role="inactive" />
                </SuiProgressRing>
            </template>

            <SuiChip label="Tutorium Datenbanksysteme" hex="#e1d5e8" />

            <template #footer>
                <button class="button kpi-button">Details</button>
            </template>
        </SuiCard>

        <SuiCard orientation="horizontal" subtitle="Status Monatsblätter" title="Ausstehend">
            <template #visual>
                <StudipIcon shape="exclaim" :size="48" role="attention" />
            </template>

            <SuiChip label="Tutorium Datenbanksysteme" hex="#e1d5e8" />

            <template #footer>
                <button class="button kpi-button">Monatsblatt einreichen</button>
            </template>
        </SuiCard>
        <SuiCard orientation="horizontal" subtitle="Urlaubstage" title="12 /30 Tage verfügbar">
            <template #visual>
                <SuiProgressRing :value="18" :max="30" :size="48" :strokeWidth="4">
                    <StudipIcon shape="schedule" :size="20" role="inactive" />
                </SuiProgressRing>
            </template>

            <SuiChip label="Tutorium Datenbanksysteme" hex="#e1d5e8" />

            <template #footer>
                <button class="button kpi-button">Antrag stellen</button>
            </template>
        </SuiCard>
                <SuiCard orientation="horizontal" subtitle="Geleistete Stunden" title="28 / 40 h">
            <template #visual>
                <SuiProgressRing :value="30.5" :max="160" :size="48" :strokeWidth="4">
                    <StudipIcon shape="date" :size="20" role="inactive" />
                </SuiProgressRing>
            </template>

            <SuiChip label="Tutorium Informatik I" hex="#d0ebea" />

            <template #footer>
                <button class="button kpi-button">Details</button>
            </template>
        </SuiCard>

        <SuiCard orientation="horizontal" subtitle="Urlaubstage" title="28 /30 Tage verfügbar">
            <template #visual>
                <SuiProgressRing :value="18" :max="30" :size="48" :strokeWidth="4">
                    <StudipIcon shape="schedule" :size="20" role="inactive" />
                </SuiProgressRing>
            </template>

            <SuiChip label="Tutorium Informatik I" hex="#d0ebea" />

            <template #footer>
                <button class="button kpi-button">Antrag stellen</button>
            </template>
        </SuiCard>
    </div>
</template>

<script setup>
import SuiCard from '../ui/SuiCard.vue';
import StudipIcon from '../ui/StudipIcon.vue';
import SuiProgressRing from '../ui/SuiProgressRing.vue';
import SuiChip from '../ui/SuiChip.vue';
</script>

<style lang="scss" scoped>
.worker-overview-kpis {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1rem;
}

.kpi-meta {
    font-size: 0.75rem;
    color: var(--color--font-secondary, #666);
}

button.button.kpi-button {
    margin: 0;
}
</style>