export function parseBoolean(value, fallback = false) {
    if (typeof value === 'boolean') return value;
    if (value === null || value === undefined) return fallback;
    if (typeof value === 'number') return value !== 0;
    if (typeof value === 'string') {
        const normalized = value.trim().toLowerCase();
        if (['true', '1', 'yes', 'on'].includes(normalized)) return true;
        if (['false', '0', 'no', 'off', ''].includes(normalized)) return false;
    }
    return fallback;
}

export function stripGlobalBoxSizing() {
    const cleanElement = (el) => {
        if (!el || typeof el.textContent !== 'string') return;
        const cleaned = el.textContent.replace(
            /\*\s*,\s*::before\s*,\s*::after\s*\{\s*box-sizing\s*:\s*border-box\s*;\s*\}/g,
            ''
        );
        if (cleaned !== el.textContent) {
            el.textContent = cleaned;
        }
    };

    const existing = document.querySelector('style[data-primevue-style-id="global-style"]');
    cleanElement(existing);

    const observer = new MutationObserver(() => {
        const target = document.querySelector('style[data-primevue-style-id="global-style"]');
        cleanElement(target);
    });

    observer.observe(document.head, {
        childList: true,
        subtree: true,
        characterData: true,
    });
}