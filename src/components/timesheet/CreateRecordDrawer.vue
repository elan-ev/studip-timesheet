<template>
    <SuiDrawer
        :title="$gettext('Arbeitszeit Erfassung')"
        side="right"
        :with-overlay="true"
        :dismissible="false"
        :show-close-button="true"
    >
        <template #anchor>
            <SuiButton :label="$gettext('Arbeitszeit erfassen')" icon="add"/>
        </template>

        <template #default="{ close }">
            <form >
                <input type="text">
                <input type="text">
                <input type="text">
                <input type="text">
                <input type="text">
            </form>
        </template>

        <template #footer>
            <SuiButton :label="$gettext('Speichern')" icon="add"/>
            <SuiButton :label="$gettext('Abbrechen')" icon="decline"/>
        </template>

    </SuiDrawer>
</template>

<script setup>
import { ref, computed, onMounted, reactive } from 'vue';
import SuiDrawer from '../ui/SuiDrawer.vue';
import SuiButton from '../ui/SuiButton.vue';

const props = defineProps({
    contracts: {
        type: Array,
        default: () => []
    },
    institutes: {
        type: Array,
        default: () => []
    },
});

const emit = defineEmits(['record-created']);

const formData = reactive({ firstname: '', lastname: '' });

const onFormSubmit = () => {

};

</script>

<style lang="scss" scoped>

</style>
