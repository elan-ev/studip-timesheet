<template>
    <div class="timesheet-worker-overview">
        <header class="timesheet-worker-overview__header">
            <WorkerOverviewFilterBar />
        </header>
        <section class="timesheet-worker-overview__kpis">
            <WorkerOverviewKpis />
        </section>
        <section class="timesheet-worker-overview__timeline">
            <WorkerOverviewTimeline />
        </section>
        <section class="timesheet-worker-overview__calendar">
            <WorkerOverviewCalendar />
        </section>
    </div>
</template>

<script setup>
import WorkerOverviewFilterBar from './WorkerOverviewFilterBar.vue';
import WorkerOverviewKpis from './WorkerOverviewKpis.vue';
import WorkerOverviewTimeline from './WorkerOverviewTimeline.vue';
import WorkerOverviewCalendar from './WorkerOverviewCalendar.vue';

</script>

<style lang="scss">
.timesheet-worker-overview {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;

    &__kpis {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1rem;
    }
}
</style>