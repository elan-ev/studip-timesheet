import { ref, computed } from 'vue';
import { defineStore } from 'pinia';
import { api } from './api/kitsu-api.js';

export const useSupervisorStore = defineStore('supervisorStore', () => {
    const records = ref(new Map());
    const isLoading = ref(false);
    const errors = ref(false);

    function prepareRequestConfig(includePaths = []) {
        const config = {};
        if (includePaths.length > 0) {
            config.params = {
                include: includePaths.join(','),
            };
        }
        return config;
    }

    function storeRecord(newRecord) {
        records.value.set(String(newRecord.id), newRecord);
        records.value = new Map(records.value);
    }

    function storeRecords(newRecords) {
        if (!newRecords || newRecords.length === 0) return;
        newRecords.forEach((supervisor) => {
            records.value.set(String(supervisor.id), supervisor);
        });
        records.value = new Map(records.value);
    }

    function clearRecords() {
        records.value = new Map();
    }

    function clearErrors() {
        errors.value = false;
    }

    const all = computed(() => {
        void records.value.size;
        return Array.from(records.value.values());
    });

    function byId(id) {
        void records.value.size;
        return records.value.get(String(id));
    }

    function byUserId(userId) {
        void records.value.size;

        return Array.from(records.value.values()).filter((record) => {
            return String(record['user-id']) === String(userId);
        });
    }

    function byContractId(contractId) {
        void records.value.size;

        return Array.from(records.value.values()).filter((record) => {
            return String(record['contract-id']) === String(contractId);
        });
    }

    async function fetchById(id, includePaths = []) {
        isLoading.value = true;
        clearErrors();
        try {
            const config = prepareRequestConfig(includePaths);
            const { data } = await api.fetch(`timesheet-supervisors/${id}`, config);
            storeRecord(data);
        } catch (err) {
            console.error(`Error while fetching supervisor with id: ${id}`, err);
            errors.value = err;
        } finally {
            isLoading.value = false;
        }
    }

    async function fetchByContractId(contractId, includePaths = []) {
        isLoading.value = true;
        clearErrors();
        try {
            const config = prepareRequestConfig(includePaths);
            const { data } = await api.fetch(`timesheet-contracts/${contractId}/supervisors`, config);
            if (data) {
                storeRecords(data);
            }
        } catch (err) {
            console.error(`Error while fetching supervisors with contract id: ${contractId}`, err);
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
            const { data } = await api.fetch('timesheet-supervisors', config);
            if (data) {
                clearRecords();
                storeRecords(data);
            }
        } catch (err) {
            console.error('Error while fetching all supervisors', err);
            errors.value = err;
        } finally {
            isLoading.value = false;
        }
    }

    async function create(supervisorData) {
        isLoading.value = true;
        clearErrors();
        try {
            const { data } = await api.post('timesheet-supervisors', supervisorData);
            storeRecord(data);
        } catch (err) {
            console.error('Error while creating supervisor', err);
            errors.value = err;
        } finally {
            isLoading.value = false;
        }
    }

    async function remove(supervisorId, deletePermanently = false) {
        const targetId = String(supervisorId);
        const record = records.value.get(targetId);
        if (!record) return;

        records.value.delete(targetId);
        records.value = new Map(records.value);

        if (deletePermanently) {
            isLoading.value = true;
            clearErrors();
            try {
                await api.delete('timesheet-supervisors', targetId);
            } catch (err) {
                console.error(`Error while permanently deleting supervisor with id: ${targetId}`, err);
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
        byId,
        byUserId,
        byContractId,
        fetchById,
        fetchByContractId,
        fetchAll,
        create,
        remove,
    };
});