import { defineStore } from 'pinia';

export const useRecordStore = defineStore('recordStore', () => {
    const records = ref(new Map());
    const recordsBySheet = ref(new Map());
    const isLoading = ref(false);
    const isLoadingMore = ref(false);
    const errors = ref(false);

    function storeRecord(newRecord) {
        const sheetId = newRecord['sheet-id'];
        records.value.set(String(newRecord.id), newRecord);

        if (!recordsBySheet.value.has(sheetId)) {
            recordsBySheet.value.set(sheetId, []);
        }

        const sheetRecords = recordsBySheet.value.get(sheetId);
        const existingIndex = sheetRecords.findIndex((m) => m.id === newRecord.id);

        if (existingIndex > -1) {
            sheetRecords[existingIndex] = newRecord;
        } else {
            sheetRecords.push(newRecord);
        }
    }

    function storeRecords(newRecords, sheetId) {
        if (!newRecords || newRecords.length === 0) return;

        newRecords.forEach((rec) => records.value.set(rec.id, rec));

        const currentRecords = recordsBySheet.value.get(sheetId) || [];

        const updatedRecords = [...currentRecords];

        newRecords.forEach((newRecord) => {
            const existingIndex = updatedRecords.findIndex((m) => m.id === newRecord.id);
            if (existingIndex > -1) {
                updatedRecords[existingIndex] = newRecord;
            } else {
                updatedRecords.push(newRecord);
            }
        });

        recordsBySheet.value.set(sheetId, updatedRecords);
    }

    function clearRecords() {
        records.value = new Map();
        recordsBySheet.value = new Map();
    }

    function byId(id) {
        void records.value.size;
        return records.value.get(String(id));
    }

    function bySheetId(sheetId) {
        return recordsBySheet.value.get(sheetId) || [];
    }

    async function fetchBySheetId(sheetId) {
        isLoading.value = true;
        try {
            const { data, meta } = await api.fetch(`timesheet-sheets/${sheetId}/records`, {
                params: {
                    'page[offset]': 0,
                    'page[limit]': 31,
                },
            });
            storeRecords(data, sheetId);
        } catch (err) {
            console.error(`Error while fetching timesheet records for sheet with id: ${sheetId}`, err);
            errors.value = err;
        } finally {
            isLoading.value = false;
        }
    }

    async function fetchById(id) {
        isLoading.value = true;
        try {
            const { data } = await api.get(`timesheet-records/${id}`);
            storeRecord(data);
        } catch (err) {
            console.error(`Error while fetching record with id: ${id}`, err);
            errors.value = err;
        } finally {
            isLoading.value = false;
        }
    }

    return {
        records,
        isLoading,
        isLoadingMore,
        errors,
        storeRecord,
        storeRecords,
        clearRecords,
        byId,
        bySheetId,
        fetchBySheetId,
        fetchById
    };
});
