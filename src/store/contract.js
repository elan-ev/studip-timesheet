import { ref } from 'vue';
import { defineStore } from 'pinia';
import { api } from './api/kitsu-api.js';

export const useContractStore = defineStore('contractStore', () => {
    const records = ref(new Map());
    const contractsByInstitute = ref(new Map());
    const paginationByInstitute = ref(new Map());
    const contractsByEmployee = ref(new Map());
    const employeeInstitutes = ref(new Map());

    const isLoading = ref(false);
    const isLoadingMore = ref(false);
    const errors = ref(false);

    function getPaginationForInstitute(instituteId) {
        if (!paginationByInstitute.value.has(instituteId)) {
            paginationByInstitute.value.set(instituteId, {
                offset: 0,
                limit: 30,
                total: 0,
                hasMore: true,
            });
        }
        return paginationByInstitute.value.get(instituteId);
    }

    function storeRecord(newRecord) {
        const instituteId = newRecord['institute-id'];
        records.value.set(String(newRecord.id), newRecord);

        if (!contractsByInstitute.value.has(instituteId)) {
            contractsByInstitute.value.set(instituteId, []);
        }

        const instituteRecords = contractsByInstitute.value.get(instituteId);
        const existingIndex = instituteRecords.findIndex((m) => m.id === newRecord.id);

        if (existingIndex > -1) {
            instituteRecords[existingIndex] = newRecord;
        } else {
            instituteRecords.push(newRecord);
        }
    }

    function storeRecordsForEmployee(newRecords, userId) {
        if (!newRecords || newRecords.length === 0) return;

        newRecords.forEach((rec) => records.value.set(rec.id, rec));

        const currentRecords = contractsByEmployee.value.get(userId) || [];
        const currentEmployeeInstitutes = employeeInstitutes.value.get(userId) || [];

        const updatedRecords = [...currentRecords];
        const updatedInstitutes = [...currentEmployeeInstitutes];

        newRecords.forEach((newRecord) => {
            const existingIndex = updatedRecords.findIndex((m) => m.id === newRecord.id);
            if (existingIndex > -1) {
                updatedRecords[existingIndex] = newRecord;
            } else {
                updatedRecords.push(newRecord);
            }

            // We also want to compile a list of institutes out of employ records.
            const contractInst = newRecord?.institute?.data || null;
            if (contractInst) {
                const existingInstituteIndex = updatedInstitutes.findIndex((m) => m.id === contractInst.id);
                if (existingInstituteIndex > -1) {
                    updatedInstitutes[existingInstituteIndex] = contractInst;
                } else {
                    updatedInstitutes.push(contractInst);
                }
            }
        });

        contractsByEmployee.value.set(userId, updatedRecords);
        employeeInstitutes.value.set(userId, updatedInstitutes);
    }

    function storeRecords(newRecords, instituteId) {
        if (!newRecords || newRecords.length === 0) return;

        newRecords.forEach((rec) => records.value.set(rec.id, rec));

        const currentRecords = contractsByInstitute.value.get(instituteId) || [];

        const updatedRecords = [...currentRecords];

        newRecords.forEach((newRecord) => {
            const existingIndex = updatedRecords.findIndex((m) => m.id === newRecord.id);
            if (existingIndex > -1) {
                updatedRecords[existingIndex] = newRecord;
            } else {
                updatedRecords.push(newRecord);
            }
        });

        contractsByInstitute.value.set(instituteId, updatedRecords);
    }

    function clearRecords() {
        records.value = new Map();
        contractsByInstitute.value = new Map();
        contractsByEmployee.value = new Map();
        employeeInstitutes.value = new Map();
    }

    function byId(id) {
        void records.value.size;
        return records.value.get(String(id));
    }

    function byInstituteId(instituteId) {
        void contractsByInstitute.value.size;
        return contractsByInstitute.value.get(instituteId) || [];
    }

    function byEmployeeId(employeeId) {
        void contractsByEmployee.value.size;
        return contractsByEmployee.value.get(employeeId) || [];
    }

    function instituteByEmployeeId(employeeId) {
        void employeeInstitutes.value.size;
        return employeeInstitutes.value.get(employeeId) || [];
    }

    async function fetchByInstituteId(instituteId, { loadMore = false } = {}) {
        const pagination = getPaginationForInstitute(instituteId);
        if (loadMore && (!pagination.hasMore || isLoadingMore.value)) {
            return;
        }

        if (loadMore) {
            isLoadingMore.value = true;
        } else {
            isLoading.value = true;
        }

        const currentOffset = loadMore ? pagination.offset + pagination.limit : 0;
        try {
            const { data, meta } = await api.fetch(`institutes/${instituteId}/timesheet-contracts`, {
                params: {
                    'page[offset]': currentOffset,
                    'page[limit]': pagination.limit,
                },
            });
            storeRecords(data, instituteId);
            if (meta.page) {
                const total = meta.page.total ?? 0;
                const offset = meta.page.offset ?? currentOffset;
                const limit = meta.page.limit ?? pagination.limit;
                const hasMore = meta.page.hasMore ?? false;

                paginationByInstitute.value.set(instituteId, {
                    offset,
                    limit,
                    total,
                    hasMore,
                });
            }
        } catch (err) {
            console.error(`Error while fetching timesheet contracts for institute with id: ${instituteId}`, err);
            errors.value = err;
        } finally {
            isLoading.value = false;
            isLoadingMore.value = false;
        }
    }

    async function fetchById(id) {
        isLoading.value = true;
        try {
            const { data } = await api.get(`timesheet-contracts/${id}`);
            storeRecord(data);
        } catch (err) {
            console.error(`Error while fetching contract with id: ${id}`, err);
            errors.value = err;
        } finally {
            isLoading.value = false;
        }
    }

    async function update(contractId, contractData) {
        isLoading.value = true;
        try {
            const { data } = await api.patch('timesheet-contracts', contractData);
            storeRecord(data);
        } catch (err) {
            console.error(`Error while updating contract with id: ${contractId}`, err);
            errors.value = err;
        } finally {
            isLoading.value = false;
        }
    }

    async function create(contractData) {
        isLoading.value = true;
        try {
            const { data } = await api.post('timesheet-contracts', contractData);
            storeRecord(data);
        } catch (err) {
            console.error('Error while creating contract', err);
            errors.value = err;
        } finally {
            isLoading.value = false;
        }
    }

    async function remove(contractId, deletePermanently = false) {
        const record = records.value.get(contractId);
        if (!record) return;

        const instituteId = record['institute-id'];
        records.value.delete(String(contractId));
        if (contractsByInstitute.value.has(instituteId)) {
            const data = contractsByInstitute.value.get(instituteId);
            const filtered = data.filter((m) => m.id !== contractId);
            contractsByInstitute.value.set(instituteId, filtered);
        }

        const employeeId = record['employee-id'];
        if (contractsByEmployee.value.has(employeeId)) {
            const employeeData = contractsByEmployee.value.get(employeeId);
            const filteredEmployeeData = employeeData.filter((m) => m.id !== contractId);
            contractsByEmployee.value.set(employeeId, filteredEmployeeData);

            // Also filter employee institutes.
            const existingEmployeeInstIds = filteredEmployeeData.length > 0 ?
                filteredEmployeeData.map((c) => c['institute-id']) : [];

            if (employeeInstitutes.value.has(employeeId)) {
                const employeeInstData = employeeInstitutes.value.get(employeeId);
                const filteredEmployeeInstData = employeeInstData.filter((inst) => existingEmployeeInstIds.includes(inst.id));
                employeeInstitutes.value.set(employeeId, filteredEmployeeInstData);
            }
        }

        if (deletePermanently) {
            isLoading.value = true;
            try {
                await api.delete('timesheet-contracts', instituteId);
            } catch (err) {
                console.error(`Error while permanently deleting contract with id: ${instituteId}`, err);
                errors.value = err;
            } finally {
                isLoading.value = false;
            }
        }
    }

    async function fetchByEmployee(userId) {
        isLoading.value = true;
        try {
            const config = prepareRequestConfig(['institute', 'sheets']);
            const { data } = await api.fetch(`employee/${userId}/timesheet-contracts`, config);
            storeRecordsForEmployee(data, userId);
        } catch (err) {
            console.error(`Error while fetching timesheet contracts for employee with user id: ${userId}`, err);
            errors.value = err;
        } finally {
            isLoading.value = false;
        }
    }

    function prepareRequestConfig(includePaths = []) {
        const config = {};
        if (includePaths.length > 0) {
            config.params = {
                include: includePaths.join(','),
            };
        }
        return config;
    }

    return {
        records,
        contractsByInstitute,
        paginationByInstitute,
        isLoading,
        isLoadingMore,
        errors,
        contractsByEmployee,
        employeeInstitutes,

        getPaginationForInstitute,
        storeRecord,
        storeRecords,
        clearRecords,
        byId,
        byInstituteId,
        fetchByInstituteId,
        fetchById,
        update,
        create,
        remove,
        fetchByEmployee,
        byEmployeeId,
        instituteByEmployeeId,
    };
});
