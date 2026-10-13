import { computed, ref, watch } from 'vue';

const KEY = 'appearance';
const appearance = ref('system');

if (typeof window !== 'undefined') {
    appearance.value = localStorage.getItem(KEY) || 'system';
}

const resolvedAppearance = computed(() => {
    if (appearance.value !== 'system') return appearance.value;

    if (typeof window === 'undefined') return 'light';
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
});

const syncDocumentClass = () => {
    if (typeof document === 'undefined') return;

    document.documentElement.classList.toggle('dark', resolvedAppearance.value === 'dark');
};

watch([appearance, resolvedAppearance], () => {
    if (typeof window !== 'undefined') {
        localStorage.setItem(KEY, appearance.value);
    }
    syncDocumentClass();
}, { immediate: true });

export const useAppearance = () => {
    const updateAppearance = (value) => {
        appearance.value = value || 'system';
    };

    return {
        appearance,
        resolvedAppearance,
        updateAppearance,
    };
};
