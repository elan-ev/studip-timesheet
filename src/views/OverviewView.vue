<script setup>
import { computed, ref } from 'vue';
import { useContextStore } from '../store/context';
import { useTimesheetStore } from '../store/timesheet';

import SuiProgressSpinner from '../components/ui/SuiProgressSpinner.vue';
import SuiStateHelper from '../components/ui/SuiStateHelper.vue';

import TheWorkerOverview from '../components/timesheet/TheWorkerOverview.vue';
import TheSupervisorOverview from '../components/timesheet/TheSupervisorOverview.vue';

const contextStore = useContextStore();
const timesheetStore = useTimesheetStore();

const isLoading = ref(false);

const isSupervisor = computed(() => {
    return contextStore.isSupervisor;
});

const isWorker = computed(() => {
    return contextStore.isWorker;
});
</script>

<template>
    <div id="timesheet-overview-view">
        <div v-if="isLoading" class="timesheet-overview-loading">
            <SuiProgressSpinner :duration="1.6" :size="64" color="#676767" />
            <p class="timesheet-overview-loading-message">{{ $gettext('Lade Einstellungen...') }}</p>
        </div>
        <template v-else>
            <TheWorkerOverview v-if="isWorker" />
            <TheSupervisorOverview v-if="isSupervisor" />
            <SuiStateHelper v-if="!isWorker && !isSupervisor" type="info" iconName="exclaim"
                :title="$gettext('Kein Zugriff')"
                :description="$gettext('Sie haben keinen Zugriff auf diese Funktion')" />
        </template>
    </div>
</template>

<style lang="scss">
#timesheet-overview-view {
    position: relative;
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;

    .timesheet-overview-loading {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        flex: 1 1 auto;
        width: 100%;
        min-height: 300px;
        gap: 1rem;
        text-align: center;

        .timesheet-overview-loading-message {
            margin: 0;
            color: var(--color-text-subtle, #676767);
            font-size: 0.95rem;
        }
    }
}
</style>