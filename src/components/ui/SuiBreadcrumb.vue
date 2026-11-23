<template>
    <nav v-if="items.length" class="sui-breadcrumb" :aria-label="computedAriaLabel">
        <ol class="sui-breadcrumb__list">
            <template v-for="(item, index) in items" :key="item.id">
                <slot :item="item" :index="index" :current="index === items.length - 1">
                    <sui-breadcrumb-item
                        :label="item.label"
                        :to="item.to"
                        :icon="item.icon"
                        :icon-only="item.iconOnly"
                        :link-type="linkType"
                        :separator="separator"
                        :show-separator="index > 0"
                        :current="index === items.length - 1"
                        @navigate="handleNavigate($event, item)"
                    />
                </slot>
            </template>
        </ol>
    </nav>
</template>

<script setup>
import { computed, getCurrentInstance } from 'vue'
import SuiBreadcrumbItem from './SuiBreadcrumbItem.vue'

const props = defineProps({
    items: {
        type: Array,
        default: () => [],
        validator: (items) => {
            const ids = items.map((item) => item.id)
            const allDefined = ids.every((id) => id !== undefined && id !== null)
            const allUnique = new Set(ids).size === ids.length
            return allDefined && allUnique
        }
    },
    linkType: {
        type: String,
        default: 'anchor',
        validator: (value) => ['anchor', 'router'].includes(value),
    },
    separator: {
        type: String,
        default: 'chevron',
        validator: (value) => ['chevron', 'slash'].includes(value),
    },
    ariaLabel: {
        type: String,
        default: undefined,
    },
})

const emit = defineEmits(['navigate'])

const { proxy } = getCurrentInstance()
const $gettext = proxy?.$gettext ?? ((str) => str)

const computedAriaLabel = computed(() => {
    return props.ariaLabel ?? $gettext('Navigationspfad')
})

const handleNavigate = ({ originalEvent }, item) => {
    emit('navigate', { originalEvent, item })
}
</script>

<style lang="scss" scoped>
.sui-breadcrumb {
    &__list {
        display: flex;
        flex-wrap: wrap;
        margin: 0;
        padding: 0;
        list-style: none;
    }
}
</style>
