<template>
  <div class="timesheet-worker-overview">
    <header class="timesheet-worker-overview__header">
      <h1>{{ $gettext('Zeiterfassung') }}</h1>
    </header>

    <section class="timesheet-worker-overview__kpis">
      <WorkerOverviewKpis />
    </section>

    <div class="timesheet-worker-overview__main">
      <section class="timesheet-worker-overview__timeline">
        <WorkerOverviewTimeline />
      </section>

      <section class="timesheet-worker-overview__calendar">
        <WorkerOverviewCalendar />
      </section>
    </div>
  </div>
</template>

<script setup>
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

  &__main {
    display: flex;
    flex-direction: column;
    gap: 4rem;
    align-items: stretch;

    @media (min-width: 1024px) {
      flex-direction: row;
      margin-top: 2rem;
    }
  }

  &__timeline {
    flex: 1 1 0%;
    width: 100%;
    min-width: 0;
    display: flex;

    > * {
      width: 100%;
      height: 100%;
    }
  }

  &__calendar {
    flex: 0 0 405px;
    width: 100%;
    min-width: 0;
    display: flex;

    > * {
      width: 100%;
      height: 100%;
    }
  }
}
</style>