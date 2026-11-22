<template>
    <div :class="['sui-field-group', { 'sui-field-group--disabled': disabled, 'sui-field-group--invalid': showError }]">

        <label v-if="label" :id="`${uuid}-label`" :for="`${uuid}-input`" class="sui-input-label">
            {{ label }}
            <span v-if="isRequired" class="sui-field-required" aria-hidden="true">*</span>
        </label>

        <div class="sui-datetime-container">
            <PrimeDatePicker :id="`${uuid}-input`" v-model="model" :dateFormat="computedDateFormat" :view="viewMode"
                :selectionMode="selectionMode" :maxDateCount="maxDateCount" :showTime="includeTime" :timeOnly="timeOnly"
                :hourFormat="hourFormat" :stepHour="stepHour" :stepMinute="stepMinute" :stepSecond="stepSecond"
                :showSeconds="showSeconds" :minDate="minDate" :maxDate="maxDate" :disabledDates="disabledDates"
                :disabledDays="disabledDays" :disabled="disabled" :placeholder="computedPlaceholder" :unstyled="true"
                :pt="ptConfig" v-bind="$attrs" :showButtonBar="isButtonBarVisible" :showIcon="showIcon" :numberOfMonths="numberOfMonths"
                :showWeek="showWeek" iconDisplay="input" :showClear="clearable" @input="clearErrorOnInput">

                <template #clearicon="{ clearCallback }">
                    <SuiButton icon="decline" :icon-only="true" :icon-only-unstyled="true" size="normal"
                        class="sui-datetime-input-clear-btn" @click.stop="handleClear(clearCallback)" />
                </template>
                <template #inputicon>
                    <SuiIcon :shape="iconShape" size="16" class="sui-input-icon" />
                </template>

                <template #prevbutton="{ actionCallback }">
                    <SuiButton iconOnlyUnstyled iconOnly icon="arr_1left" data-pc-group-section="navigator"
                        class="sui-datetime-navigator" @click.prevent="actionCallback" />
                </template>
                <template #nextbutton="{ actionCallback }">
                    <SuiButton iconOnlyUnstyled iconOnly icon="arr_1right" data-pc-group-section="navigator"
                        class="sui-datetime-navigator" @click.prevent="actionCallback" />
                </template>
                <template #incrementicon>
                    <SuiIcon shape="arr_1up" />
                </template>
                <template #decrementicon>
                    <SuiIcon shape="arr_1down" />
                </template>

                <template #buttonbar="{ todayCallback, clearCallback }">
                    <slot name="buttonbar" :today-callback="todayCallback" :clear-callback="clearCallback">
                        <SuiButton v-if="showToday" variant="text" @click.prevent="todayCallback">
                            Heute
                        </SuiButton>

                        <SuiButton v-if="showClear" variant="text" @click.prevent="clearCallback">
                            Leeren
                        </SuiButton>
                    </slot>
                </template>
            </PrimeDatePicker>
            <span v-if="showError" :id="`${uuid}-error`" class="sui-input-error-message">
                {{ internalError }}
            </span>
            <span v-if="!showError && hint !== ''" :id="`${uuid}-hint`" class="sui-input-hint">
                {{ hint }}
            </span>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, useId, inject, onMounted, onBeforeUnmount, useSlots } from 'vue';
import * as v from 'valibot';
import PrimeDatePicker from 'primevue/datepicker';
import SuiIcon from './SuiIcon.vue';
import SuiButton from './SuiButton.vue';


defineOptions({
    name: 'SuiInputDateTime',
    isSuiFormField: true,
    inheritAttrs: false
});

const slots = useSlots();
const props = defineProps({
    label: { type: String, default: '' },
    required: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
    hint: { type: String, default: '' },
    rules: { type: [Object, Array], default: () => [] },
    baseSchema: {
        type: Function,
        default: () => v.any() // Für flexible Typen (Date, Array, String) im DatePicker
    },
    placeholder: { type: String, default: '' },
    icon: { type: String, default: null },
    showIcon: { type: Boolean, default: true },
    clearable: { type: Boolean, default: false },
    mode: {
        type: String,
        default: 'date',
        validator: (v) => ['date', 'datetime', 'time', 'month', 'year'].includes(v)
    },

    selectionMode: { type: String, default: 'single' },
    maxDateCount: { type: Number, default: null },

    stepHour: { type: Number, default: 1 },
    stepMinute: { type: Number, default: 5 },
    stepSecond: { type: Number, default: 1 },
    showSeconds: { type: Boolean, default: false },
    hourFormat: { type: String, default: '24' },

    minDate: { type: Date, default: null },
    maxDate: { type: Date, default: null },

    disabledDates: { type: Array, default: null },
    disabledDays: { type: Array, default: null },

    showToday: { type: Boolean, default: false },
    showClear: { type: Boolean, default: false },

    showWeek: { type: Boolean, default: true },
    numberOfMonths: { type: Number, default: 1 },
});

const uuid = useId();
const model = defineModel({ type: [Date, Array, String], default: null });
const internalError = ref('');
const wasValidated = ref(false);
const showError = computed(() => wasValidated.value && !!internalError.value);
const isRequired = computed(() => props.required);

const timeOnly = computed(() => props.mode === 'time');
const includeTime = computed(() => props.mode === 'datetime' || props.mode === 'time');
const viewMode = computed(() => {
    if (props.mode === 'month') return 'month';
    if (props.mode === 'year') return 'year';
    return 'date';
});

const isButtonBarVisible = computed(() => {
    return props.showToday || props.showClear || !!slots.buttonbar;
});

const computedPlaceholder = computed(() => {
    if (props.selectionMode === 'range') {
        const start = model.value?.[0];
        const end = model.value?.[1];

        if (start && end) {
            const options = { day: '2-digit', month: '2-digit', year: 'numeric' };

            if (props.mode === 'datetime') {
                options.hour = '2-digit';
                options.minute = '2-digit';
                if (props.showSeconds) options.second = '2-digit';
            }

            return `${start.toLocaleDateString('de-DE', options)} - ${end.toLocaleDateString('de-DE', options)}`;
        }
        return 'Startdatum - Enddatum';
    }

    if (props.placeholder) return props.placeholder;
    if (props.mode === 'time') return props.showSeconds ? 'HH:MM:SS' : 'HH:MM';
    if (props.mode === 'datetime') return props.showSeconds ? 'TT.MM.JJJJ HH:MM:SS' : 'TT.MM.JJJJ HH:MM';
    if (props.mode === 'month') return 'MM.JJJJ';
    if (props.mode === 'year') return 'JJJJ';
    return 'TT.MM.JJJJ';
});
const computedDateFormat = computed(() => {
    if (props.mode === 'month') return 'mm.yy';
    if (props.mode === 'year') return 'yy';
    return 'dd.mm.yy';
});

const iconShape = computed(() => {
    if (props.icon) {
        return props.icon;
    }
    if (timeOnly.value) {
        return 'date';
    }
    return 'schedule2';
});

const ptConfig = computed(() => ({
    root: { class: 'sui-datetime-picker sui-input-wrapper' },
    pcInputText: {
        root: {
            class: 'sui-input sui-datetime-input',
            'aria-labelledby': props.label ? `${uuid}-label` : undefined,
            'aria-describedby': props.hint ? `${uuid}-hint` : undefined,
            'aria-required': props.required ? 'true' : undefined
        }
    },
    inputIconContainer: {
        class: 'sui-datetime-input-adornments sui-datetime-input-adornments--main'
    },

    panel: {
        class: [
            'sui-datetime-panel',
            { 'sui-datetime-panel--time-only': timeOnly.value }
        ]
    },
    header: { class: 'sui-datetime-panel-header' },
    title: { class: 'sui-datetime-panel-title' },
    selectMonth: { class: 'sui-datetime-select-link sui-datetime-select-link--month' },
    selectYear: { class: 'sui-datetime-select-link sui-datetime-select-link--year' },

    calendarContainer: {  class: 'sui-datetime-calendar' },
    dayView: { class: 'sui-datetime-table' },
    monthView: { class: 'sui-datetime-month-list' },
    yearView: { class: 'sui-datetime-year-list' },

    tableHeader: { class: 'sui-datetime-table-header' },
    tableHeaderCell: { class: 'sui-datetime-th' },
    tableBody: { class: 'sui-datetime-table-body' },
    tableBodyRow: { class: 'sui-datetime-tr' },

    weekHeaderLabel: { class: 'sui-datetime-table-week-header' },
    weekHeader: { class: 'sui-datetime-th sui-datetime-th--week' },
    weekNumber: { class: 'sui-datetime-td sui-datetime-td--week' },

    dayCell: { class: 'sui-datetime-td' },

    weekLabelContainer: { class: 'sui-datetime-kw' },

    day: ({ context }) => ({
        class: [
            'sui-datetime-day',
            { 'is-today': context.today },
            { 'is-selected': context.selected },
            { 'is-disabled': context.disabled },
            { 'is-other-month': context.otherMonth }
        ]
    }),
    month: ({ context }) => ({
        class: [
            'sui-datetime-month',
            { 'is-selected': context.selected },
            { 'is-disabled': context.disabled }
        ]
    }),
    year: ({ context }) => ({
        class: [
            'sui-datetime-year',
            { 'is-selected': context.selected },
            { 'is-disabled': context.disabled }
        ]
    }),
    timePicker: { class: 'sui-datetime-time-picker' },
    hourPicker: { class: 'sui-datetime-time-unit sui-datetime-time-unit--hour' },
    minutePicker: { class: 'sui-datetime-time-unit sui-datetime-time-unit--minute' },
    secondPicker: { class: 'sui-datetime-time-unit sui-datetime-time-unit--second' },
    pcIncrementButton: { root: { class: 'sui-datetime-time-btn sui-datetime-time-btn--inc' } },
    pcDecrementButton: { root: { class: 'sui-datetime-time-btn sui-datetime-time-btn--dec' } },

    buttonbar: { class: 'sui-datetime-buttonbar' },
}));

const hasValue = computed(() => {
    if (!model.value) return false;
    if (Array.isArray(model.value)) return model.value.length > 0 && model.value[0] !== null;
    return model.value !== '';
});

const formContext = inject('SuiFormContext', null);
const validateField = () => {
    reset();
    if (!hasValue.value) {
        if (props.required) {
            internalError.value = 'Dieses Feld darf nicht leer sein.';
            return false;
        }
        return true;
    }

    if (!props.rules || (Array.isArray(props.rules) && props.rules.length === 0)) return true;
    const rulesArray = Array.isArray(props.rules) ? props.rules : [props.rules];

    try {
        for (const rule of rulesArray.filter(Boolean)) {
            v.parse(v.pipe(props.baseSchema(), rule), model.value);
        }
        return true;
    } catch (error) {
        if (error instanceof v.ValiError) {
            internalError.value = error.issues[0].message;
        }
        return false;
    }
};

const handleClear = (primevueClearCallback) => {
    primevueClearCallback();
    if (wasValidated.value) {
        reset();
        validateField();
    }
};

const clearErrorOnInput = () => {
    if (wasValidated.value && validateField()) {
        reset();
    }
};

const reset = () => {
    internalError.value = '';
};

const formInterface = {
    label: props.label,
    validate: () => {
        wasValidated.value = true;
        return validateField();
    },
    getError: () => internalError.value,
    focus: () => {
        const el = document.getElementById(`${uuid}-input`);
        el?.focus();
    },
    reset
};

onMounted(() => formContext?.registerField(formInterface));
onBeforeUnmount(() => formContext?.unregisterField(formInterface));

</script>

<style lang="scss">
:root {
    --sui-dt-bg: var(--color--content-box-background, #ffffff);
    --sui-dt-border: var(--color--content-box-border, #d0d2d3);
    --sui-dt-text: var(--color--font-primary, #1d1f20);
    --sui-dt-text-secondary: var(--color--font-secondary, #6b7280);
    --sui-dt-primary: var(--color--highlight, #28497c);
    --sui-dt-primary-light: var(--color--highlight-light, #e8effa);
    --sui-dt-hover: var(--color--activity-background, #f3f4f6);
}

.sui-field-group {
    &--disabled {
        opacity: 0.6;
        pointer-events: none;
    }
}

.sui-datetime-picker {
    position: relative;
    display: flex;
    align-items: center;
    width: 100%;
    max-width: 16em;

    .sui-datetime-input {
        width: 100%;
        padding: 8px 12px;
        padding-right: 32px;
        border: 1px solid var(--color--content-box-border, #d0d2d3);
        border-radius: 4px;
        transition: padding-right 0.2s ease;

        &:focus {
            outline: none;
            border-color: var(--color--highlight, #28497c);
        }
    }

    &.is-clearable {
        .sui-datetime-input {
            padding-right: 56px;
        }
    }

    .sui-datetime-input-clear-btn {
        position: absolute;
        right: 36px;
        padding: 0;
        background: transparent;
        border: none;
        cursor: pointer;
    }

    .sui-datetime-input-adornments {
        position: absolute;
        right: 16px;
        display: flex;
        align-items: center;
        pointer-events: none;
        color: var(--color--font-secondary);

    }
}

.sui-datetime-panel {
    background: var(--sui-dt-bg);
    border: 1px solid var(--sui-dt-border);
    border-radius: 6px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    padding: 12px;
    margin-top: 4px;
    z-index: 1000;
    width: max-content !important;
    min-width: 256px;
    max-width: 100vw;
    box-sizing: border-box;

    .sui-datetime-panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 8px;
        padding-bottom: 8px;
        border-bottom: 1px solid var(--sui-dt-hover);

        .sui-datetime-panel-title {
            display: flex;
            gap: 4px;

            .sui-datetime-select-link {
                background: none;
                border: none;
                padding: 4px;
                min-width: unset;
                aspect-ratio: auto;
                font-weight: 700;
                font-size: 14px;
                color: var(--color--highlight);

                &:hover:not(:disabled) {
                    background: none;
                    color: var(--color--highlight-hover);
                    text-decoration: underline;
                }
            }
        }

        .sui-datetime-navigator {
            margin: 0;
            justify-content: center;
            align-items: center;

            &:hover {
                background: var(--sui-dt-hover);
            }
        }
    }

    .sui-datetime-calendar {
        display: flex;
        gap: 32px;
        flex-wrap: wrap;
        justify-content: center;
    }
}

/* Die Kalender-Tabelle */
.sui-datetime-table {
    width: 100%;
    border-collapse: collapse;
    border-spacing: 0 4px;
    margin-top: 8px;

    .sui-datetime-th {
        font-size: 11px;
        font-weight: 700;
        color: var(--sui-dt-text-secondary);
        padding: 6px 0;
        text-align: center;
        width: calc((100% - 28px) / 7);
    }

    .sui-datetime-th--week {
        padding-right: 8px;
        border-right: 1px solid var(--sui-dt-hover);
    }

    .sui-datetime-td {
        padding: 0;
        text-align: center;
    }

    .sui-datetime-td--week {
        padding-right: 12px;
        border-right: 1px solid var(--sui-dt-hover);
    }

    .sui-datetime-kw,
    .sui-datetime-day {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        height: 36px;
        font-size: 13px;
    }

    .sui-datetime-kw {
        color: var(--sui-dt-text-secondary);
    }

    .sui-datetime-day {
        width: 36px;
        font-weight: 500;
        cursor: pointer;
        color: var(--sui-dt-text);
        transition: background-color 0.15s, color 0.15s;

        &:hover {
            background: var(--sui-dt-hover);
        }

        &.is-today {
            border: 1px solid var(--sui-dt-primary);
            font-weight: 700;
            color: var(--sui-dt-primary);
        }

        &.is-selected {
            background: var(--sui-dt-primary) !important;
            color: #ffffff !important;
            font-weight: 600;
        }

        &.is-other-month,
        &.is-disabled {
            color: var(--sui-dt-text-secondary);
            opacity: 0.35;
        }

        &.is-disabled {
            cursor: not-allowed;
            pointer-events: none;
        }
    }
}

.sui-datetime-time-picker {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 12px;
    border-top: 1px solid var(--sui-dt-border-light, #eaeaea);
    padding-top: 12px;
    margin-top: 12px;

    .sui-datetime-time-unit {
        display: flex;
        flex-direction: column;
        align-items: center;

        span {
            font-size: 16px;
            padding: 4px 0;
            min-width: 24px;
            text-align: center;
        }
    }

    .sui-datetime-time-btn {
        background: transparent;
        border: none;
        cursor: pointer;
        padding: 4px 8px;
        border-radius: 4px;
        color: var(--color--highlight);

        &:hover {
            background: var(--sui-dt-hover);
        }
    }
}

.sui-datetime-panel--time-only .sui-datetime-time-picker {
    border: none;
    padding-top: 0;
    margin-top: 0;
}

/* Monats- und Jahresauswahl-Raster */
.sui-datetime-month-list,
.sui-datetime-year-list {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
    padding: 8px 0;
    margin-top: 8px;
}

/* Die einzelnen Kacheln (Monat / Jahr) */
.sui-datetime-month,
.sui-datetime-year {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 8px 4px;
    font-size: 13px;
    font-weight: 500;
    border-radius: 4px;
    cursor: pointer;
    color: var(--sui-dt-text);
    transition: background-color 0.15s, color 0.15s;

    &:hover {
        background: var(--sui-dt-hover);
    }

    &.is-selected {
        background: var(--sui-dt-primary) !important;
        color: #ffffff !important;
        font-weight: 600;
    }

    &.is-disabled {
        color: var(--sui-dt-text-secondary);
        opacity: 0.35;
        cursor: not-allowed;
        pointer-events: none;
    }
}

.sui-datetime-year-list {
    grid-template-columns: repeat(4, 1fr);
    max-height: 200px;
    overflow-y: auto;
}

.sui-datetime-buttonbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0 0 0;
    margin-top: 8px;
    border-top: 1px solid var(--sui-dt-border-light, #eaeaea);

    &>button:only-child {
        margin-left: auto;
    }
}



/* duplicate!!! */

.sui-field-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
    max-width: 43em;
    margin-bottom: 1em;

    .sui-input-label {
        font-weight: 600;
        font-size: 14px;
        color: var(--color--font-primary);

        .sui-field-required {
            color: var(--color--danger, #b91c1c);
            margin-left: 2px;
        }
    }

    .sui-input-hint {
        font-size: 12px;
        color: var(--color--font-secondary);
        margin-top: 2px;
    }

    .sui-input-error-message {
        font-size: 12px;
        color: var(--color--danger, #b91c1c);
        margin-top: 2px;
    }

    &.sui-field-group--invalid {
        .sui-input {
            border-color: var(--color--danger, #b91c1c);
            background-color: var(--color--danger-surface, #fef2f2);
        }
    }
}
</style>
