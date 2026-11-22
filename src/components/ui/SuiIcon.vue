<template>
    <svg v-if="spriteLoaded" :class="computedClass" :style="colorStyle" :width="`${size}px`" :height="`${size}px`"
        fill="currentColor" :aria-hidden="ariaHidden" :role="ariaRole" :aria-label="ariaLabel || undefined">
        <use :href="`#icon-${shape}`" />
    </svg>
    <img v-else :src="url" :width="size" :height="size" v-bind="$attrs">
</template>

<script setup>
import { computed, ref, onMounted } from 'vue'

const props = defineProps({
    shape: { type: String, required: true },
    size: { type: Number, default: 20 },
    inline: { type: Boolean, default: false },
    class: { type: String, default: '', alias: 'customClass' },
    role: { type: String, default: '', alias: 'iconRole' },
    hex: {
        type: String,
        default: '',
        validator: (value) => value === '' || /^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$/.test(value),
    },
    ariaLabel: { type: String, default: '' },
    withSprite: { type: Boolean, default: false },
})

const iconRoleColorMap = {
    clickable: 'highlight',
    navigation: 'highlight',
    sort: 'highlight',
    accept: 'good',
    attention: 'warning',
    info: 'font-primary',
    info_alt: 'font-inverted',
    inactive: 'font-inactive',
    new: 'red-1',
    'status-green': 'green-1',
    'status-red': 'red-1',
    'status-yellow': 'yellow-1',
}
const iconRoleColorMapLegacy = {
    info: 'black',
    inactive: 'grey',
    accept: 'green',
    'status-green': 'green',
    attention: 'red',
    new: 'red',
    'status-red': 'red',
    info_alt: 'white',
    sort: 'yellow',
    'status-yellow': 'yellow',
    'lightblue': 'lightblue',
    clickable: 'blue',
    navigation: 'blue',
}

const isDecorative = computed(() => !props.ariaLabel || props.ariaLabel.trim() === '')
const ariaHidden = computed(() => isDecorative.value)
const ariaRole = computed(() => (isDecorative.value ? undefined : 'img'))
const computedClass = computed(() => [
    'sui-icon',
    `sui-icon--${props.shape}`,
    { 'sui-icon--inline': props.inline },
    props.class,
])
const colorStyle = computed(() => {
    if (props.hex) {
        return { color: props.hex }
    }
    if (props.role) {
        // iconRole
        const colorVar = iconRoleColorMap[props.role]
        return colorVar ? { color: `var(--color--${colorVar})` } : {}
    }
    return {}
})

const computedLegacyColor = computed(() => {
    const role = props?.role || 'clickable';
    return iconRoleColorMapLegacy[role];
})

const url = computed(() => {
    if (props.shape.indexOf("http") === 0) {
        return props.shape;
    }
    let path = props.shape.split('+').reverse().join('/');

    if (props.shape == 'clipboard') {
        return window.OpencastPlugin.PLUGIN_ASSET_URL + '/images/clipboard.svg';
    } else {
        return `${STUDIP.ASSETS_URL}images/icons/${computedLegacyColor.value}/${path}.svg`;
    }
})

const spriteLoaded = ref(false)

const loadSprite = async () => {
    if (window.__STUDIP_ICON_SPRITE_LOADED__) {
        spriteLoaded.value = true
        return
    }

    if (!document.getElementById('svg-sprite')) {
        const spritePath = window.STUDIP?.URLHelper?.getURL
            ? window.STUDIP.URLHelper.getURL('assets/images/icons/icons.svg', {}, true)
            : './assets/images/icons/icons.svg'

        const res = await fetch(spritePath)
        const svgText = await res.text()
        const div = document.createElement('div')
        div.innerHTML = svgText
        const svg = div.querySelector('svg')
        svg.id = 'svg-sprite'
        svg.style.display = 'none'
        document.body.prepend(svg)
    }

    window.__STUDIP_ICON_SPRITE_LOADED__ = true
    spriteLoaded.value = true
}

onMounted(() => {
    if (props.withSprite) loadSprite()
})
</script>
