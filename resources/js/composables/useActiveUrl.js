import { usePage } from '@inertiajs/vue3';

const normalize = (value) => {
    if (!value) return '/';

    try {
        const url = new URL(value, window.location.origin);
        const path = url.pathname.replace(/\/$/, '');
        return path || '/';
    } catch {
        const path = String(value).split('?')[0].replace(/\/$/, '');
        return path || '/';
    }
};

export const useActiveUrl = () => {
    const page = usePage();

    const urlIsActive = (href) => {
        const current = normalize(page.url);
        const target = normalize(typeof href === 'string' ? href : href?.url);
        return current === target || current.startsWith(`${target}/`);
    };

    return { urlIsActive };
};
