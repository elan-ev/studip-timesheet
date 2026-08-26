<template>
  <div class="sui-card" :class="[`sui-card--${orientation}`]">
    <div v-if="$slots.header" class="sui-card__header">
      <slot name="header" />
    </div>

    <div class="sui-card__body">
      <div v-if="$slots.visual" class="sui-card__visual">
        <slot name="visual" />
      </div>

      <div class="sui-card__main">
        <div v-if="title || subtitle || $slots.title || $slots.subtitle" class="sui-card__caption">
          <div v-if="subtitle || $slots.subtitle" class="sui-card__subtitle">
            <slot name="subtitle">{{ subtitle }}</slot>
          </div>
          <div v-if="title || $slots.title" class="sui-card__title">
            <slot name="title">{{ title }}</slot>
          </div>
        </div>

        <div v-if="$slots.default" class="sui-card__content">
          <slot />
        </div>

        <div v-if="$slots.footer" class="sui-card__footer">
          <slot name="footer" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    default: undefined,
  },
  subtitle: {
    type: String,
    default: undefined,
  },
  orientation: {
    type: String,
    default: 'portrait',
    validator: (val) => ['portrait', 'horizontal'].includes(val),
  },
})
</script>

<style lang="scss" scoped>
.sui-card {
  background: var(--color--white, #ffffff);
  border: 1px solid var(--color--content-border, #dcdcdc);
  border-radius: var(--border-radius--base, 4px);
  display: flex;
  flex-direction: column;
  overflow: hidden;

  &__header {
    width: 100%;

    :deep(img) {
      width: 100%;
      height: auto;
      display: block;
    }
  }

  &__body {
    padding: 0.875rem 1.25rem;
    display: flex;
    gap: 2rem;
    flex-grow: 1;
  }

  &--portrait &__body {
    flex-direction: column;
  }

  &--portrait &__visual {
    display: flex;
    align-items: center;
    justify-content: flex-start;
  }

  &--horizontal &__body {
    flex-direction: row;
    align-items: flex-start;
  }

  &--horizontal &__visual {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }

  &__main {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    flex-grow: 1;
    min-width: 0;
    align-self: stretch; 
  }

  &__caption {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
  }

  &__title {
    font-size: 1.3rem;
    font-weight: 700;
    line-height: 1.3;
    color: var(--color--font-primary, #333333);
  }

  &__subtitle {
    font-size: .875rem;
    color: var(--color--font-secondary, #666666);
  }

  &__content {
    flex-grow: 1;
  }

  &__footer {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 0.5rem;
    padding-top: 1rem;
    margin-top: auto;
  }
}
</style>