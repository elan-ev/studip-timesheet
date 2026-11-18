// useSlotFilter.js
import { isVNode, Comment } from 'vue'

export function useSlotFilter(slots) {
    /**
     * @param {string|function} filterCriteria - Komponentenname oder eine Funktion (vnode) => boolean
     */
    const getValidChildren = (filterCriteria) => {
        const rawNodes = slots.default ? slots.default() : []

        return rawNodes.filter((vnode) => {
            if (!isVNode(vnode) || vnode.type === Comment) return false

            if (typeof filterCriteria === 'function') {
                return filterCriteria(vnode)
            }

            const componentName = vnode.type?.name || vnode.type?.__name
            const isCorrectComponent = componentName === filterCriteria

            if (!isCorrectComponent && vnode.type !== Symbol.for('v-fgt') && import.meta.env.DEV) {
                console.warn(
                    `[UI-Kit] Element vom Typ "${componentName || vnode.type}" wurde entfernt.`,
                )
            }
            return isCorrectComponent
        })
    }

    return { getValidChildren }
}