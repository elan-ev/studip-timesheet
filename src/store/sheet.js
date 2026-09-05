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

    function byContractId(contractId) {
        void records.value.size;

        return Array.from(records.value.values()).filter((record) => {
            return String(record['contract-id']) === String(contractId);
        });
    }

    async function fetchById(id, includePaths = []) {
        isLoading.value = true;
        errors.value = false;
        try {
            const config = prepareRequestConfig(includePaths);
            const { data } = await api.fetch(`timesheet-sheets/${id}`, config);
            storeRecord(data);
        } catch (err) {
            console.error(`Error while fetching sheet with id: ${id}`, err);
            errors.value = err;
        } finally {
            isLoading.value = false;
        }
    }

    async function fetchByContractId(contractId, includePaths = []) {
        isLoading.value = true;
        errors.value = false;
        try {
            const config = prepareRequestConfig(includePaths);
            const { data } = await api.fetch(`timesheet-contracts/${contractId}/sheets`, config);
            if (data) {
                clearRecords();
                data.forEach((sheet) => {
                    storeRecord(sheet);
                });
            }
        } catch (err) {
            console.error(`Error while fetching sheets with contract id: ${contractId}`, err);
            errors.value = err;
        } finally {
            isLoading.value = false;
        }

    }

    async function fetchAll(includePaths = []) {
        isLoading.value = true;
        clearErrors();
        try {
            const config = prepareRequestConfig(includePaths);
            const { data } = await api.fetch('timesheet-sheets', config);
            if (data) {
                clearRecords();
                data.forEach((sheet) => {
                    storeRecord(sheet);
                });
            }
        } catch (err) {
            console.error('Error while fetching all sheets', err);
            errors.value = err;
        } finally {
            isLoading.value = false;
        }
    }

    async function update(sheetId, sheetData) {
        isLoading.value = true;
        try {
            const { data } = await api.patch('timesheet-sheets', sheetData);
            data.id = sheetId;
            storeRecord(data);
        } catch (err) {
            console.error(`Error while updating sheet with id: ${sheetId}`, err);
            errors.value = err;
        } finally {
            isLoading.value = false;
        }
    }

    async function create(sheetData) {
        isLoading.value = true;
        try {
            const { data } = await api.post('timesheet-sheets', sheetData);
            storeRecord(data);
        } catch (err) {
            console.error('Error while creating sheet', err);
            errors.value = err;
        } finally {
            isLoading.value = false;
        }
    }

    async function remove(sheetId, deletePermanently = false) {
        const record = records.value.get(sheetId);
        if (!record) return;

        records.value.delete(String(sheetId));

        if (deletePermanently) {
            isLoading.value = true;
            try {
                await api.delete('timesheet-sheets', sheetId);
            } catch (err) {
                console.error(`Error while permanently deleting sheet with id: ${sheetId}`, err);
                errors.value = err;
            } finally {
                isLoading.value = false;
            }
        }

    }



    return {
        records,
        isLoading,
        errors,

        all,
        storeRecord,
        clearRecords,
        clearErrors,
        byId,
        byContractId,
        fetchById,
        fetchByContractId,
        fetchAll,
        update,
        create,
        remove
    };
});
