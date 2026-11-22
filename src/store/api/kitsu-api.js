import Kitsu from 'kitsu';

const toKebabCase = (str) =>
    str.replace(/([a-z0-9])([A-Z])/g, '$1-$2').toLowerCase();

const keysToKebabCase = (data) => {
    if (data === null || typeof data !== 'object' || data instanceof Date) {
        return data;
    }
    if (Array.isArray(data)) {
        return data.map(keysToKebabCase);
    }
    return Object.fromEntries(
        Object.entries(data).map(([key, value]) => [
            toKebabCase(key),
            keysToKebabCase(value),
        ])
    );
};

const attributeCase = 'kebab'; // "kebab" | "snake" | "none"

const absoluteUriStudip = new URL(window.STUDIP.ABSOLUTE_URI_STUDIP);
const baseURL = `${absoluteUriStudip.pathname}jsonapi.php/v1/`;
const api = new Kitsu({ baseURL, camelCaseTypes: false, pluralize: false, resourceCase: 'kebab', attributeCase });

// For Kitsu 11.0.1 there is no `attributeCase`, but in later versions it was introduced, so we now use our built-in kebab convertor, until we upgrade it later.
if (attributeCase === 'kebab') {
    api.interceptors.request.use((config) => {
        if (!config.data) return config;

        const isString = typeof config.data === 'string';
        const body = isString ? JSON.parse(config.data) : config.data;

        if (body?.data?.attributes) {
            body.data.attributes = keysToKebabCase(body.data.attributes);
            config.data = isString ? JSON.stringify(body) : body;
        }

        return config;
    });
}

export { api };
