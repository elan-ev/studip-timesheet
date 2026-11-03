import { computed, ref } from 'vue';
import { defineStore } from 'pinia';

export const useContextStore = defineStore('contextStore', () => {
    const userId = ref(null);
    const preferredLanguage = ref('de_DE');
    const isWorker = ref(false);
    const isSupervisor = ref(false);
    const isGlobalAdmin = ref(false);
    const hasPermissions = ref(false);

    const languageIsGerman = computed(() => preferredLanguage.value.toLowerCase().startsWith('de'));

    const langSelector = computed(() => {
        return languageIsGerman.value ? 'de' : 'en';
    });

    function setUserId(id) {
        userId.value = id;
    }

    function setPreferredLanguage(language) {
        preferredLanguage.value = language;
    }

    function setIsWorker(state) {
        isWorker.value = state;
    }

    function setIsSupervisor(state) {
        isSupervisor.value = state;
    }

    function setIsGlobalAdmin(state) {
        isGlobalAdmin.value = state;
    }

    function setHasPermissions(state) {
        hasPermissions.value = state;
    }

    return {
        userId,
        preferredLanguage,
        isWorker,
        isSupervisor,
        isGlobalAdmin,
        hasPermissions,
        langSelector,
        setUserId,
        setPreferredLanguage,
        setIsWorker,
        setIsSupervisor,
        setIsGlobalAdmin,
        setHasPermissions
    };
});
