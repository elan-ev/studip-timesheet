import { ref, computed } from 'vue';
import { defineStore } from 'pinia';
import { api } from './api/kitsu-api.js';

export const useSheetStore = defineStore('sheetStore', () => {
    const records = ref(new Map());
    const isLoading = ref(false);
    const errors = ref(false);

    function storeRecord(newRecord) {
        records.value.set(String(newRecord.id), newRecord);
    }

    function clearRecords() {
        records.value = new Map();
    }

    function clearErrors() {
        errors.value = false;
    }

    const all = computed(() => {
        void records.value.size;
        return [...records.value.values()];
    });

    function byId(id) {
        void records.value.size;
        return records.value.get(String(id));
    }

    async function byUserId(userId) {

    }

    async function byFilters(filters = []) {
        
    }

    async function fetchById(id, includePaths = []) {
    }

    async function fetchByUserId(userId, includePaths = []) {

    }

    async function fetchAll(filters = [], includePaths = []) {
        
    }

    async function createSheet() {

    }

    async function deleteSheet(id) {

    }

    async function updateSheet(id) {

    }

    return {
        records,
        storeRecord,
        clearRecords,
        removeRecord,
        clearErrors,
        isLoading,
        errors,
        all,
        byId,
        byUserId,
        fetchById,
        fetchByUserId,
        fetchAll,
        createSheet,
        deleteSheet,
        updateSheet

    };
});
