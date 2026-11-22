<template>
    <div class="admin-overview">
        <header class="admin-overview__header">
            <h1>{{ $gettext('Zeiterfassung') }}</h1>
            <p class="admin-overview__intro">
                {{ $gettext('Wählen Sie einen Bereich aus, um Verträge und Stundennachweise zu prüfen oder Benutzerberechtigungen zu verwalten.') }}
            </p>
        </header>

        <nav class="admin-overview__grid" :aria-label="$gettext('Verwaltungsbereiche')">
            <router-link
                :to="{ name: 'admin-contracts-overview' }"
                class="admin-overview__card"
            >
                <div class="admin-overview__card-content">
                    <h2>{{ $gettext('Verträge') }}</h2>
                    <p>
                        {{ $gettext('Laufende und abgeschlossene Verträge einsehen, neue Verträge anlegen sowie monatliche Stundennachweise prüfen und freigeben.') }}
                    </p>
                </div>
                <span class="admin-overview__card-action" aria-hidden="true">
                    {{ $gettext('Zu den Verträgen') }} →
                </span>
            </router-link>

            <router-link
                :to="{ name: 'admin-permissions' }"
                class="admin-overview__card"
            >
                <div class="admin-overview__card-content">
                    <h2>{{ $gettext('Berechtigungen') }}</h2>
                    <p>
                        {{ $gettext('Zugriffsrechte und Zuständigkeiten für Institute verwalten und festlegen, wer Verträge und Stundennachweise einsehen darf.') }}
                    </p>
                </div>
                <span class="admin-overview__card-action" aria-hidden="true">
                    {{ $gettext('Zu den Berechtigungen') }} →
                </span>
            </router-link>
        </nav>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';


</script>

<style scoped lang="scss">
.admin-overview {
    max-width: 1100px;
    margin: 1rem 0;
}

.admin-overview__header {
    margin-bottom: 1.5rem;

    h1 {
        margin: 0 0 0.5rem 0;
    }
}

.admin-overview__intro {
    margin: 0;
    color: var(--base-color-60, #555);
}

.admin-overview__grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.admin-overview__card {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 1.5rem;
    background-color: var(--white, #fff);
    border: 1px solid var(--content-color-20, #d0d7de);
    border-radius: 4px;
    text-decoration: none;
    color: inherit;
    transition: border-color 0.15s ease, box-shadow 0.15s ease, transform 0.1s ease;

    &:hover,
    &:focus-visible {
        border-color: var(--base-color, #28497c);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        transform: translateY(-2px);
        outline: none;
    }
}

.admin-overview__card-content {
    h2 {
        margin: 0 0 0.75rem 0;
        color: var(--base-color, #28497c);
        font-size: 1.25rem;
    }

    p {
        margin: 0 0 1.25rem 0;
        line-height: 1.5;
        color: var(--black, #222);
    }
}

.admin-overview__card-action {
    font-weight: 600;
    color: var(--base-color, #28497c);
}
</style>