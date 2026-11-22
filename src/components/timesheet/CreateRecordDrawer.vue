<template>
    <SuiDrawer
        :title="$gettext('Arbeitszeit Erfassung')"
        side="right"
        :with-overlay="true"
        :dismissible="false"
        :show-close-button="true"
        ref="drawerRef"
    >
        <template #anchor>
            <SuiButton :label="$gettext('Arbeitszeit erfassen')" icon="add"/>
        </template>

        <template #default="{ close }">
            <template v-if="isLoading">
                <SuiProgressIndicator class="timesheet-create-record-loading" spinner-color-hex="#536d96">
                    {{ $gettext('Formular wird geladen...') }}
                </SuiProgressIndicator>
            </template>
            <SuiStateHelper v-else-if="errors" type="error" iconName="exclaim" :title="$gettext('Fehler')"
                :description="$gettext('Bei der Erstellung des Datensatzes ist ein Fehler aufgetreten.')" />
            <SuiForm class="timesheet-create-record-form" v-else ref="formRef" id="create-record-form" @submit="onFormSubmit" >
                <SuiSelect
                    v-model="selectedInstitute"
                    :options="institutes"
                    :disabled="institutes.length <= 1"
                    :required="true"
                    optionLabel="name"
                    :placeholder="$gettext('Nach institution suchen..')"
                    :label="$gettext('Institution')"
                    :filter="true"
                    :filterPlaceholder="$gettext('Institutionsname eingeben...')"
                    :emptyFilterMessage="$gettext('Kein Institute gefunden!')"
                >
                    <template #option="slotProps">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <SuiIcon shape="institute" size="16" style="color: var(--color--font-secondary);" />
                            <span>{{ slotProps.option.name }}</span>
                        </div>
                    </template>
                </SuiSelect>
                <SuiSelect
                    v-model="selectedContract"
                    :options="contracts"
                    :disabled="contracts.length <= 1"
                    :required="true"
                    optionLabel="label"
                    :placeholder="$gettext('Vertrag wählen..')"
                    :label="$gettext('Vertrag')"
                />
                <SuiSelect
                    v-model="selectedSheet"
                    :options="sheets"
                    :disabled="sheets.length <= 1"
                    :required="true"
                    optionLabel="label"
                    :placeholder="$gettext('Sheet wählen')"
                    :label="$gettext('Sheet')"
                />
                <SuiInputDateTime
                    :required="true"
                    :label="$gettext('Datum')"
                    :show-today="true"
                    :show-clear="true"
                    :show-week="true"
                    :min-date="computedSheetMinDate"
                    :max-date="computedSheetMaxDate"
                    mode="date"
                    v-model="formData.date"
                />
                <SuiInputDateTime
                    :required="true"
                    :label="$gettext('Start Um')"
                    mode="time"
                    v-model="formData.startTime"
                    :step-minute="5"
                    :rules="[startBeforeEndRule]"
                    :max-date="formData.endTime ?? null"
                />
                <SuiInputDateTime
                    :required="true"
                    :label="$gettext('Ende Um')"
                    mode="time"
                    v-model="formData.endTime"
                    :step-minute="5"
                    :rules="[endAfterStartRule]"
                    :min-date="formData.startTime ?? null"
                />
                <SuiInputDateTime
                    :label="$gettext('Pause Begin Um')"
                    mode="time"
                    v-model="formData.breakStart"
                    :max-date="formData.endTime ?? null"
                    :min-date="formData.startTime ?? null"
                />
                <SuiInputNumber
                    :label="$gettext('Pausedauer')"
                    v-model="formData.breakDuration"
                    :suffix="' ' + $gettext('Minuten')"
                    :rules="[breakIsInRange]"
                />
                <SuiSelect
                    v-model="formData.absenceType"
                    :options="absenceTypes"
                    :required="true"
                    optionLabel="value"
                    :placeholder="$gettext('Typ wählen')"
                    :label="$gettext('Typ')"
                />
                <SuiInputText
                    :label="$gettext('Kommentar')"
                    v-model="formData.comment"
                />
            </SuiForm>
        </template>

        <template #footer>
            <div class="timesheet-create-record-btn-container">
                <SuiButton type="submit" form="create-record-form" :label="$gettext('Speichern')" icon="add"/>
                <SuiButton :label="$gettext('Abbrechen')" icon="decline" @click="onCancel"/>
            </div>
        </template>
    </SuiDrawer>
</template>

<script setup>
import { ref, computed, useTemplateRef, watch, watchEffect, getCurrentInstance, toRaw } from 'vue';
import SuiDrawer from '../ui/SuiDrawer.vue';
import SuiButton from '../ui/SuiButton.vue';
import SuiForm from '../ui/SuiForm.vue';
import SuiInputText from '../ui/SuiInputText.vue';
import SuiInputNumber from '../ui/SuiInputNumber.vue';
import SuiInputDateTime from '../ui/SuiInputDateTime.vue';
import SuiSelect from '../ui/SuiSelect.vue';
import SuiProgressIndicator from '../ui/SuiProgressIndicator.vue';
import SuiStateHelper from '../ui/SuiStateHelper.vue';

import { useContractStore } from '../../store/contract';
import { useContextStore } from '../../store/context';
import { useSheetStore } from '../../store/sheet';
import { useRecordStore } from '../../store/record';
import * as v from 'valibot';

const contractStore = useContractStore();
const contextStore = useContextStore();
const sheetStore = useSheetStore();
const recordStore = useRecordStore();

const props = defineProps({
    userId: {
        type: String,
        required: true,
    },
    institutes: {
        type: Array,
        required: true,
    }
});

const emit = defineEmits(['record-created']);

const { proxy } = getCurrentInstance();
const $gettext = proxy?.$gettext ?? ((str) => str);

const selectedInstitute = ref(null);
const selectedContract = ref(null);
const selectedSheet = ref(null);

const drawerRef = useTemplateRef('drawerRef');
const formRef = useTemplateRef('formRef');

const startBeforeEndRule = v.check(
    (startDateTime) => {
        if (
            startDateTime instanceof Date
            && formData.value.endTime instanceof Date
            && formData.value.endTime.getTime() > startDateTime.getTime()
        ) {
            return true;
        }
        return false;
    },
    $gettext('Die Startzeit muss vor der Endzeit liegen')
);

const endAfterStartRule = v.check(
    (endDateTime) => {
        if (
            endDateTime instanceof Date
            && formData.value.startTime instanceof Date
            && formData.value.startTime.getTime() < endDateTime.getTime()
        ) {
            return true;
        }
        return false;
    },
    $gettext('Die Endzeit muss nach der Startzeit liegen.')
);

const breakIsInRange = v.check(
    (breakMinutes) => {
        // TODO: recheck the validation here!
        if (breakMinutes === 0 || formData.value.breakStart === null) return true;
        let endOfBreakDate = new Date(formData.value.breakStart.getTime() + Number(breakMinutes || 0) * 60_000);
        if (
            formData.value.breakStart instanceof Date
            && formData.value.endTime instanceof Date
            && endOfBreakDate.getTime() <= formData.value.endTime.getTime()
        ) {
            return true;
        }
        return false;
    },
    $gettext('Die Pause muss vor Ablauf der Zeit beendet sein.')
);

const getInitFormData = () => ({
    sheetId: '',
    date: null,
    startTime: null,
    endTime: null,
    breakStart: null,
    breakDuration: 0,
    absenceType: null,
    comment: '',
});

const formData = ref(getInitFormData());

const isLoading = computed(() => {
    return contractStore.isLoading || sheetStore.isLoading || recordStore.isLoading;
});

const errors = computed(() => {
    return contractStore.errors || sheetStore.errors || recordStore.errors;
});

const isDrawerOpen = computed(() => {
    return drawerRef?.value?.isOpen || false;
});

const contracts = computed(() => {
    return selectedInstitute?.value?.id ? contractStore.byInstituteId(selectedInstitute.value.id) : [];
});

const sheets = computed(() => {
    if (!selectedContract?.value?.id) return [];
    const rawSheets = sheetStore.byContractId(selectedContract.value.id) || [];

    return rawSheets.map((sheet) => ({
        ...sheet,
        label: `${sheet.month}-${sheet.year}`,
    }));
});

const absenceTypes = computed(() => {
    return recordStore.absenceTypes;
});

const defaultAbsenceType = computed(() => {
    const workAbt = absenceTypes.value.filter((abt) => abt.id === 'work');
    return workAbt.length === 1 ? workAbt[0] : null;
});

const computedSheetMinDate = computed(() => {
    if (!selectedSheet.value?.year || !selectedSheet.value?.month) {
        return null;
    }

    const year = Number(selectedSheet.value.year);
    const monthIndex = Number(selectedSheet.value.month) - 1;

    return new Date(year, monthIndex, 1, 0, 0, 0, 0);
});

const computedSheetMaxDate = computed(() => {
    if (!selectedSheet.value?.year || !selectedSheet.value?.month) {
        return null;
    }

    const year = Number(selectedSheet.value.year);
    const monthIndex = Number(selectedSheet.value.month) - 1;

    return new Date(year, monthIndex + 1, 0, 23, 59, 59, 999);
})
const onCancel = () => {
    reset();
    drawerRef.value.close();
};

const onFormSubmit = async () => {
    formData.value.sheetId = Number(selectedSheet.value.id);
    const rawFormData = toRaw(toRaw(formData.value));
    rawFormData.absenceType = formData.value.absenceType.id;
    await recordStore.createRecord(rawFormData);
    if (!errors.value) {
        emit('record-created');
        reset();
        drawerRef.value.close();
    }
};

const reset = () => {
    selectedInstitute.value = null;
    selectedContract.value = null;
    selectedSheet.value = null;
    formData.value = getInitFormData();
}

watch(isDrawerOpen, async (isOpen) => {
    if (isOpen) {
        if (props.institutes.length === 1) {
            selectedInstitute.value = props.institutes[0];
        }
    } else {
        reset();
    }
});

watchEffect(() => {
    if (contracts.value.length === 1) {
        selectedContract.value = contracts.value[0];
    }
    if (sheets.value.length === 1) {
        selectedSheet.value = sheets.value[0];
    }
    if (defaultAbsenceType.value !== null) {
        formData.value.absenceType = defaultAbsenceType.value;
    }
});
</script>

<style lang="scss" scoped>
.timesheet-create-record-form {
    min-width: 450px;
}
.timesheet-create-record-btn-container {
    width: 100%;
    display: flex;
    justify-content: center;
    gap: 10px;
}
.timesheet-create-record-loading {
    height: 100%;
}
</style>
