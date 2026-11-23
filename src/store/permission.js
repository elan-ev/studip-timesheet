// import { ref } from 'vue';
// import { defineStore } from 'pinia';
// import { api } from './api/kitsu-api.js';

// export const usePermissionStore = defineStore('permissionStore', () => {
//     const records = ref(new Map());
//     const permissionsByUser = ref(new Map());
//     const isLoading = ref(false);
//     const errors = ref(false);
    
//     function storeRecord(newRecord) {
//         const userId = newRecord['user-id'] || newRecord.userId;
//         records.value.set(String(newRecord.id), newRecord);

//         if (!userId) return;

//         if (!permissionsByUser.value.has(userId)) {
//             permissionsByUser.value.set(userId, []);
//         }

//         const userRecords = permissionsByUser.value.get(userId);
//         const existingIndex = userRecords.findIndex((m) => m.id === newRecord.id);

//         if (existingIndex > -1) {
//             userRecords[existingIndex] = newRecord;
//         } else {
//             userRecords.push(newRecord);
//         }
//     }

//     function storeRecords(newRecords, userId) {
//         if (!newRecords || newRecords.length === 0) return;

//         newRecords.forEach((rec) => records.value.set(String(rec.id), rec));

//         if (!userId) return;

//         const currentRecords = permissionsByUser.value.get(userId) || [];
//         const updatedRecords = [...currentRecords];

//         newRecords.forEach((newRecord) => {
//             const existingIndex = updatedRecords.findIndex((m) => m.id === newRecord.id);
//             if (existingIndex > -1) {
//                 updatedRecords[existingIndex] = newRecord;
//             } else {
//                 updatedRecords.push(newRecord);
//             }
//         });

//         permissionsByUser.value.set(userId, updatedRecords);
//     }

//     function clearRecords() {
//         records.value = new Map();
//         permissionsByUser.value = new Map();
//     }

//     function byId(id) {
//         void records.value.size;
//         return records.value.get(String(id));
//     }

//     function byUserId(userId) {
//         void permissionsByUser.value.size;
//         return permissionsByUser.value.get(userId) || [];
//     }

//     async function fetchByUserId(userId) {
//         isLoading.value = true;
//         try {
//             const { data } = await api.fetch(`users/${userId}/timesheet-permissions`, {
//                 params: {},
//             });
//             storeRecords(data, userId);
//         } catch (err) {
//             console.error(`Error while fetching permissions for user with id: ${userId}`, err);
//             errors.value = err;
//         } finally {
//             isLoading.value = false;
//         }
//     }

//     async function fetchAll(filters = {}) {
//         isLoading.value = true;
//         try {
//             const params = {};
//             for (const [key, value] of Object.entries(filters)) {
//                 if (value !== undefined && value !== null && value !== '') {
//                     params[`filter[${key}]`] = value;
//                 }
//             }
//             const { data } = await api.fetch('timesheet-permissions', { params });
//             data.forEach((rec) => storeRecord(rec));
//         } catch (err) {
//             console.error('Error while fetching all timesheet permissions', err);
//             errors.value = err;
//         } finally {
//             isLoading.value = false;
//         }
//     }

//     async function fetchById(id) {
//         isLoading.value = true;
//         try {
//             const { data } = await api.get(`timesheet-permissions/${id}`);
//             storeRecord(data);
//         } catch (err) {
//             console.error(`Error while fetching permission with id: ${id}`, err);
//             errors.value = err;
//         } finally {
//             isLoading.value = false;
//         }
//     }

//     return {
//         records,
//         permissionsByUser,
//         isLoading,
//         errors,
//         storeRecord,
//         storeRecords,
//         clearRecords,
//         byId,
//         byUserId,
//         fetchByUserId,
//         fetchAll,
//         fetchById,
//     };
// });

import { ref, computed } from 'vue';
import { defineStore } from 'pinia';
import { api } from './api/kitsu-api.js';

export const usePermissionStore = defineStore('permissionStore', () => {
    const records = ref(new Map());
    const permissionsByUser = ref(new Map());
    const isLoading = ref(false);
    const errors = ref(false);

    function clearErrors() {
        errors.value = false;
    }

    function upsertInUserList(userId, newRecord) {
        if (!userId) return;

        const currentRecords = permissionsByUser.value.get(String(userId)) || [];
        const updatedRecords = [...currentRecords];
        const existingIndex = updatedRecords.findIndex((m) => String(m.id) === String(newRecord.id));

        if (existingIndex > -1) {
            updatedRecords[existingIndex] = newRecord;
        } else {
            updatedRecords.push(newRecord);
        }

        permissionsByUser.value.set(String(userId), updatedRecords);
        permissionsByUser.value = new Map(permissionsByUser.value);
    }

    function removeFromUserList(userId, permissionId) {
        if (!userId) {
            permissionsByUser.value.forEach((list, key) => {
                const filtered = list.filter((item) => String(item.id) !== String(permissionId));
                permissionsByUser.value.set(key, filtered);
            });
            permissionsByUser.value = new Map(permissionsByUser.value);
            return;
        }

        const key = String(userId);
        const currentRecords = permissionsByUser.value.get(key) || [];
        const filtered = currentRecords.filter((item) => String(item.id) !== String(permissionId));
        permissionsByUser.value.set(key, filtered);
        permissionsByUser.value = new Map(permissionsByUser.value);
    }

    function storeRecord(newRecord) {
        if (!newRecord || newRecord.id === undefined || newRecord.id === null) return;

        const recordId = String(newRecord.id);
        const userId =
            newRecord['user-id'] ??
            newRecord.userId ??
            newRecord?.attributes?.['user-id'] ??
            newRecord?.relationships?.user?.data?.id;

        records.value.set(recordId, newRecord);
        records.value = new Map(records.value);

        if (userId) {
            upsertInUserList(String(userId), newRecord);
        }
    }

    function storeRecords(newRecords, userId) {
        if (!newRecords || newRecords.length === 0) return;

        newRecords.forEach((rec) => {
            if (!rec || rec.id === undefined || rec.id === null) return;
            records.value.set(String(rec.id), rec);

            const effectiveUserId =
                userId ??
                rec['user-id'] ??
                rec.userId ??
                rec?.attributes?.['user-id'] ??
                rec?.relationships?.user?.data?.id;

            if (effectiveUserId) {
                upsertInUserList(String(effectiveUserId), rec);
            }
        });

        records.value = new Map(records.value);
    }

    function clearRecords() {
        records.value = new Map();
        permissionsByUser.value = new Map();
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
        void permissionsByUser.value.size;
        return permissionsByUser.value.get(String(userId)) || [];
    }

    async function fetchByUserId(userId) {
        isLoading.value = true;
        clearErrors();
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
        clearErrors();
        try {
            const params = {};
            for (const [key, value] of Object.entries(filters)) {
                if (value !== undefined && value !== null && value !== '') {
                    params[`filter[${key}]`] = value;
                }
            }
            const { data } = await api.fetch('timesheet-permissions', { params });
            if (Array.isArray(data)) {
                data.forEach((rec) => storeRecord(rec));
            }
        } catch (err) {
            console.error('Error while fetching all timesheet permissions', err);
            errors.value = err;
        } finally {
            isLoading.value = false;
        }
    }

    async function fetchById(id) {
        isLoading.value = true;
        clearErrors();
        try {
            const { data } = await api.fetch(`timesheet-permissions/${id}`);
            storeRecord(data);
        } catch (err) {
            console.error(`Error while fetching permission with id: ${id}`, err);
            errors.value = err;
        } finally {
            isLoading.value = false;
        }
    }

    async function create(permissionData) {
        isLoading.value = true;
        clearErrors();
        try {
            const { data } = await api.post('timesheet-permissions', permissionData);
            if (data) {
                storeRecord(data);
            }
            return data;
        } catch (err) {
            console.error('Error while creating permission', err);
            errors.value = err;
            throw err;
        } finally {
            isLoading.value = false;
        }
    }

    async function update(permissionId, permissionData) {
        isLoading.value = true;
        clearErrors();
        try {
            const payload = {
                id: String(permissionId),
                ...permissionData,
            };
            const { data } = await api.patch('timesheet-permissions', payload);
            if (data) {
                storeRecord(data);
            }
            return data;
        } catch (err) {
            console.error(`Error while updating permission with id: ${permissionId}`, err);
            errors.value = err;
            throw err;
        } finally {
            isLoading.value = false;
        }
    }

    async function remove(permissionId, deletePermanently = true) {
        const targetId = String(permissionId);
        const existing = records.value.get(targetId);
        const userId =
            existing?.['user-id'] ??
            existing?.userId ??
            existing?.attributes?.['user-id'] ??
            existing?.relationships?.user?.data?.id;

        if (existing) {
            records.value.delete(targetId);
            records.value = new Map(records.value);
            removeFromUserList(userId, targetId);
        }

        if (deletePermanently) {
            isLoading.value = true;
            clearErrors();
            try {
                await api.delete('timesheet-permissions', targetId);
            } catch (err) {
                console.error(`Error while deleting permission with id: ${targetId}`, err);
                errors.value = err;
                if (existing) {
                    storeRecord(existing);
                }
                throw err;
            } finally {
                isLoading.value = false;
            }
        }
    }

    return {
        records,
        permissionsByUser,
        isLoading,
        errors,
        all,
        storeRecord,
        storeRecords,
        clearRecords,
        clearErrors,
        byId,
        byUserId,
        fetchByUserId,
        fetchAll,
        fetchById,
        create,
        update,
        remove,
        delete: remove,
    };
});
