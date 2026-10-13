import { computed, ref, watch } from "vue";

export const useMultiSelect = () => {
    const isOpen = ref(false);
    const searchTerm = ref("");
    const searchInput = ref(null);
    const searchTimeout = ref(null);
    const isLoading = ref(false);

    const inputId = `multi-select-${Math.random().toString(36).substr(2, 9)}`;

    const selectedItems = computed(() => {
        return props.options.filter((option) =>
            modelValue.value.includes(option[props.valueKey])
        );
    });

    const displayedItems = computed(() => {
        return selectedItems.value.slice(0, props.maxTags);
    });

    const remainingCount = computed(() => {
        return Math.max(0, selectedItems.value.length - props.maxTags);
    });

    const filteredOptions = computed(() => {
        // Si está habilitada la búsqueda asíncrona, no filtramos localmente
        if (props.asyncSearch) {
            return props.options;
        }

        if (!searchTerm.value) {
            return props.options;
        }

        const search = searchTerm.value.toLowerCase();
        return props.options.filter((option) =>
            option[props.labelKey].toLowerCase().includes(search)
        );
    });

    const hasError = computed(() => {
        return Boolean(props.errorMessage);
    });

    watch(
        () => props.loading,
        (newVal) => {
            isLoading.value = newVal;
        }
    );

    const isSelected = (value) => {
        return modelValue.value.includes(value);
    };

    const toggleOption = (option) => {
        if (props.disabled) return;

        const value = option[props.valueKey];
        const currentValues = [...modelValue.value];
        const index = currentValues.indexOf(value);

        if (index > -1) {
            currentValues.splice(index, 1);
        } else {
            currentValues.push(value);
        }

        modelValue.value = currentValues;
        emit("change", currentValues);
    };

    const removeItem = (value) => {
        if (props.disabled) return;

        const currentValues = modelValue.value.filter((v) => v !== value);
        modelValue.value = currentValues;
        emit("change", currentValues);
    };

    const selectAll = () => {
        if (props.disabled) return;

        const allValues = filteredOptions.value.map(
            (option) => option[props.valueKey]
        );
        const newValues = [...new Set([...modelValue.value, ...allValues])];
        modelValue.value = newValues;
        emit("change", newValues);
    };

    const clearAll = () => {
        if (props.disabled) return;

        modelValue.value = [];
        emit("change", []);
    };

    const handleSearchInput = () => {
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
            emit("search", searchTerm.value);
        }, props.searchDebounce);
    };

    const toggleDropdown = () => {
        if (props.disabled) return;

        if (isOpen.value) {
            closeDropdown();
        } else {
            openDropdown();
        }
    };

    const openDropdown = async () => {
        if (props.disabled) return;

        isOpen.value = true;
        emit("open");

        // Si hay búsqueda asíncrona, emitir búsqueda inicial vacía
        if (props.asyncSearch && searchTerm.value !== "") {
            isLoading.value = true;
            emit("search", "");
        }

        if (props.searchable) {
            await nextTick();
            searchInput.value?.focus();
        }
    };

    const closeDropdown = () => {
        isOpen.value = false;
        searchTerm.value = "";
        isLoading.value = false;

        // Limpiar timeout pendiente
        if (searchTimeout.value) {
            clearTimeout(searchTimeout.value);
            searchTimeout.value = null;
        }

        emit("close");
    };

    const handleClickOutside = (event) => {
        if (!event.target.closest(".relative.w-full")) {
            closeDropdown();
        }
    };

    onMounted(() => {
        document.addEventListener("click", handleClickOutside);
    });

    onUnmounted(() => {
        document.removeEventListener("click", handleClickOutside);

        // Limpiar timeout al desmontar
        if (searchTimeout.value) {
            clearTimeout(searchTimeout.value);
        }
    });

    // Watch for external changes
    watch(
        () => props.options,
        () => {
            // Validar selección actual contra nuevas opciones
            if (modelValue.value.length > 0) {
                const validValues = props.options.map(
                    (opt) => opt[props.valueKey]
                );
                const filteredValues = modelValue.value.filter((val) =>
                    validValues.includes(val)
                );

                if (filteredValues.length !== modelValue.value.length) {
                    modelValue.value = filteredValues;
                    emit("change", filteredValues);
                }
            }

            // Desactivar loading cuando lleguen nuevas opciones
            if (props.asyncSearch) {
                isLoading.value = false;
            }
        },
        { deep: true }
    );
};
