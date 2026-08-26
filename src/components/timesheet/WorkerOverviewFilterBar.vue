<template>
  <div class="worker-overview-filter-bar">
    <div class="worker-overview-filter-bar__chips">
      <template v-for="contract in dummyContracts" :key="contract.id">
        <SuiChip v-if="isContractActive(contract.id)" :label="contract.label" :hex="contract.hex" :removable="true"
          @remove="emit('toggle-contract', contract.id)" />
      </template>
    </div>
    <StudipContextMenu button-shape="settings" :title="$gettext('Vertragsfilter')">
      <template #content>
        <div class="context-menu__section-group">
          <button class="context-menu__entry">
            <div class="context-menu__entry-content">
              <StudipIcon shape="accept" class="context-menu__entry-icon" />
              <div class="context-menu__entry-texts">
                <span class="context-menu__entry-label">{{ $gettext('Alle auswählen') }}</span>
              </div>
            </div>
          </button>
        </div>
        <div class="context-menu__section-group">
          <template v-for="contract in dummyContracts" :key="contract.id">
            <button class="context-menu__entry">
              <div class="context-menu__entry-content">
                <StudipIcon shape="checkbox-checked" class="context-menu__entry-icon" />
                <div class="context-menu__entry-texts">
                  <span class="context-menu__entry-label">{{ contract.label }}</span>
                </div>
              </div>
            </button>
          </template>
        </div>
      </template>
    </StudipContextMenu>
  </div>
</template>
<script setup>
import { computed } from 'vue'
import SuiChip from '../ui/SuiChip.vue'
import StudipContextMenu from '../ui/StudipContextMenu.vue'
import StudipIcon from '../ui/StudipIcon.vue'

const props = defineProps({
  contracts: {
    type: Array,
    required: true,
  },
  activeContractIds: {
    type: Array,
    required: true,
  },
})

const emit = defineEmits(['toggle-contract', 'select-all', 'deselect-all'])


const dummyContracts = computed(() => {
  return [{ id: '1', label: 'Tutorium Informatik I', hex: '#d0ebea' }, { id: '2', label: 'Tutorium Datenbanksysteme', hex: '#e1d5e8' },]
});

function isContractActive(id) {
  // return props.activeContractIds.includes(id)
  return true;
}

</script>

<style lang="scss">
.worker-overview-filter-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;

  &__chips {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
  }
}
</style>