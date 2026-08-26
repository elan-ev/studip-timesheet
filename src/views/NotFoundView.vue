<template>
    <SuiStateHelper type="info" iconName="search" :title="$gettext('Nicht gefunden')"
        :description="$gettext('Die von Ihnen angefragte Seite konnte nicht gefunden werden.')" />
</template>
<script setup>
import SuiStateHelper from '../components/ui/SuiStateHelper.vue';
</script>