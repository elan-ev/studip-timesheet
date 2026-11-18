import { ref } from 'vue';
import { defineStore } from 'pinia';
import { api } from './api/kitsu-api.js';

export const usePermissionStore = defineStore('permissionStore', () => {
    const records = ref(new Map());
    const permissionsByUser = ref(new Map());
    const isLoading = ref(false);
    const errors = ref(false);
    
    function storeRecord(newRecord) {
        const userId = newRecord['user-id'] || newRecord.userId;
        records.value.set(String(newRecord.id), newRecord);

        if (!userId) return;

        if (!permissionsByUser.value.has(userId)) {
            permissionsByUser.value.set(userId, []);
        }

        const userRecords = permissionsByUser.value.get(userId);
        const existingIndex = userRecords.findIndex((m) => m.id === newRecord.id);

        if (existingIndex > -1) {
            userRecords[existingIndex] = newRecord;
        } else {
            userRecords.push(newRecord);
        }
    }

    function storeRecords(newRecords, userId) {
        if (!newRecords || newRecords.length === 0) return;

        newRecords.forEach((rec) => records.value.set(String(rec.id), rec));

        if (!userId) return;

        const currentRecords = permissionsByUser.value.get(userId) || [];
        const updatedRecords = [...currentRecords];

        newRecords.forEach((newRecord) => {
            const existingIndex = updatedRecords.findIndex((m) => m.id === newRecord.id);
            if (existingIndex > -1) {
                updatedRecords[existingIndex] = newRecord;
            } else {
                updatedRecords.push(newRecord);
            }
        });

        permissionsByUser.value.set(userId, updatedRecords);
    }

    function clearRecords() {
        records.value = new Map();
        permissionsByUser.value = new Map();
    }

    function byId(id) {
        void records.value.size;
        return records.value.get(String(id));
    }

    function byUserId(userId) {
        void permissionsByUser.value.size;
        return permissionsByUser.value.get(userId) || [];
    }

    async function fetchByUserId(userId) {
        isLoading.value = true;
        try {
            const { data } = await api.fetch(`users/${userId}/timesheet-permissions`, {
                params: {},
            });
            storeRecords(data, userId);
        } catch (err) {
            console.error(`Error while fetching permissions for user with id: ${userId}`, err);
            errors.value = err;
        } finally {
            isLoading.value = false;
        }
    }

    async function fetchAll(filters = {}) {
        isLoading.value = true;
        try {
            const params = {};
            for (const [key, value] of Object.entries(filters)) {
                if (value !== undefined && value !== null && value !== '') {
                    params[`filter[${key}]`] = value;
                }
            }
            const { data } = await api.fetch('timesheet-permissions', { params });
            data.forEach((rec) => storeRecord(rec));
        } catch (err) {
            console.error('Error while fetching all timesheet permissions', err);
            errors.value = err;
        } finally {
            isLoading.value = false;
        }
    }

    async function fetchById(id) {
        isLoading.value = true;
        try {
            const { data } = await api.get(`timesheet-permissions/${id}`);
            storeRecord(data);
        } catch (err) {
            console.error(`Error while fetching permission with id: ${id}`, err);
            errors.value = err;
        } finally {
            isLoading.value = false;
        }
    }

    return {
        records,
        permissionsByUser,
        isLoading,
        errors,
        storeRecord,
        storeRecords,
        clearRecords,
        byId,
        byUserId,
        fetchByUserId,
        fetchAll,
        fetchById,
    };
});
