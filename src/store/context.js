import { defineStore } from 'pinia';
import { computed, ref } from 'vue';

export const useContextStore = defineStore('contextStore', () => {
    const userId = ref(null);
    const preferredLanguage = ref('de_DE');
    const isWorker = ref(false);
    const isSupervisor = ref(false);

    const languageIsGerman = computed(() => preferredLanguage.value === 'de-DE');

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

    return {
        userId,
        preferredLanguage,
        isWorker,
        isSupervisor,
        langSelector,
        setUserId,
        setPreferredLanguage,
        setIsWorker,
        setIsSupervisor,
    };
});
