<template>
  <div class="timesheet-worker-overview">
    <header class="timesheet-worker-overview__header">
      <h1>{{ $gettext('Zeiterfassung') }}</h1>
      <CreateRecordDrawer :user-id="userId" :institutes="employeeInstitutes" @record-created="onRecordCreated" />
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
import { computed, getCurrentInstance } from 'vue';
import WorkerOverviewKpis from './WorkerOverviewKpis.vue';
import WorkerOverviewTimeline from './WorkerOverviewTimeline.vue';
import WorkerOverviewCalendar from './WorkerOverviewCalendar.vue';
import CreateRecordDrawer from './CreateRecordDrawer.vue';
import { useContractStore } from '../../store/contract';
import { useContextStore } from '../../store/context';

const contractStore = useContractStore();
const contextStore = useContextStore();

const { proxy } = getCurrentInstance();
const $gettext = proxy?.$gettext ?? ((str) => str);

const userId = computed(() => {
  return contextStore.userId;
});

const employeeInstitutes = computed(() => {
  return contractStore.employeeInstitutes.get(userId.value) || [];
});

const onRecordCreated = () => {
  STUDIP.Report.success($gettext('Erfolg'), [$gettext('Der Datensatz wurde erstellt')]);
};

</script>

<style lang="scss">
.timesheet-worker-overview {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;

  &__header {
    h1 {
      margin: 0;
    }
    padding: 0.75rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

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
