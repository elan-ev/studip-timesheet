import { ref } from 'vue';
import { defineStore } from 'pinia';
import { api } from './api/kitsu-api.js';

export const useSupervisorStore = defineStore('supervisorStore', () => {
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
        errors.value = false;
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

    async function fetchByUserId(userId, includePaths = []) {
        // TODO
    }

    async function fetchByContractId(contractId, includePaths = []) {
        isLoading.value = true;
        errors.value = false;
        try {
            const config = prepareRequestConfig(includePaths);
            const { data } = await api.fetch(`timesheet-contracts/${contractId}/supervisors`, config);
            if (data) {
                data.forEach((supervisor) => {
                    storeRecord(supervisor);
                });
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
                data.forEach((supervisor) => {
                    storeRecord(supervisor);
                });
            }
        } catch (err) {
            console.error('Error while fetching all supervisors', err);
            errors.value = err;
        } finally {
            isLoading.value = false;
        }
    }

    // does't make sense yet!

    // async function update(supervisorId, supervisorData) {
    //     isLoading.value = true;
    //     try {
    //         const { data } = await api.patch('timesheet-supervisors', supervisorData);
    //         data.id = supervisorId;
    //         storeRecord(data);
    //     } catch (err) {
    //         console.error(`Error while updating supervisor with id: ${supervisorId}`, err);
    //         errors.value = err;
    //     } finally {
    //         isLoading.value = false;
    //     }
    // }

    async function create(supervisorData) {
        isLoading.value = true;
        try {
            const { data } = await api.post('timesheet-supervisors', supervisorData);
            storeRecord(data);
        } catch (err) {
            console.error('Error while creating supvervisor', err);
            errors.value = err;
        } finally {
            isLoading.value = false;
        }
    }

    async function remove(supervisorId, deletePermanently = false) {
        const record = records.value.get(supervisorId);
        if (!record) return;

        records.value.delete(String(supervisorId));

        if (deletePermanently) {
            isLoading.value = true;
            try {
                await api.delete('timesheet-supervisors', supervisorId);
            } catch (err) {
                console.error(`Error while permanently deleting supervisor with id: ${supervisorId}`, err);
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
        // fetchByUserId,
        fetchByContractId,
        fetchAll,
        // update,
        create,
        remove,
    };
});
