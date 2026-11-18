<template>
    <SuiInput
        ref="inputRef"
        type="text"
        :label="label"
        :placeholder="placeholder"
        :rules="combinedRules"
        :required="required"
        :icon="icon"
        :clearable="clearable"
        :hint="hint"
        v-bind="$attrs"
    />
</template>

<script setup>
import { ref, computed } from 'vue';
import SuiInput from './SuiInput.vue';

defineOptions({
    name: 'SuiInputText',
    isSuiFormField: true
});

const props = defineProps({
    label: { type: String, default: '' },
    placeholder: { type: String, default: '' },
    rules: { type: [Object, Array], default: () => [] },
    required: { type: Boolean, default: false },
    icon: { type: String, default: null },
    clearable: { type: Boolean, default: true },
    hint: { type: String, default: '' },
});

const inputRef = ref(null);

const combinedRules = computed(() => {
    const userRules = Array.isArray(props.rules) ? props.rules : [props.rules];

    return userRules.filter(Boolean);
});

defineExpose(new Proxy({}, {
    get(_, prop) {
        return inputRef.value?.[prop];
    }
}));
</script>
