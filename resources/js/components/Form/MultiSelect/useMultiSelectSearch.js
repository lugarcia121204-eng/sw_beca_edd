import { ref, computed, watch } from 'vue';

export function useMultiSelectSearch(props, searchTerm, isLoading) {
    const searchTimeout = ref(null);

    const filteredOptions = computed(() => {
        // Si está habilitada la búsqueda asíncrona, no filtramos localmente
        if (props.asyncSearch) {
            return props.options;
        }

        if (!searchTerm.value) {
            return props.options;
        }

        const search = searchTerm.value.toLowerCase();
        return props.options.filter(option =>
            option[props.labelKey].toLowerCase().includes(search)
        );
    });

    const handleSearchInput = (emit) => {
        if (!props.asyncSearch) return;

        if (!searchTerm.value.trim()) {
            isLoading.value = false;
            return;
        }

        if (searchTimeout.value) {
            clearTimeout(searchTimeout.value);
        }

        searchTimeout.value = setTimeout(() => {
            isLoading.value = true;
            emit('search', searchTerm.value);
        }, props.searchDebounce);
    };

    watch(
        () => props.loading,
        (newVal) => {
            isLoading.value = newVal;
        }
    );

    const clearSearchTimeout = () => {
        if (searchTimeout.value) {
            clearTimeout(searchTimeout.value);
            searchTimeout.value = null;
        }
    };

    return {
        filteredOptions,
        handleSearchInput,
        searchTimeout,
        clearSearchTimeout
    };
}