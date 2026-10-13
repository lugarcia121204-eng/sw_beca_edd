import { nextTick, onMounted, onUnmounted } from "vue";

export function useMultiSelectDropdown(
    props,
    isOpen,
    searchTerm,
    searchInput,
    isLoading,
    clearSearchTimeout,
    inputId,
    emit
) {
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

        clearSearchTimeout();

        emit("close");
    };

    const handleClickOutside = (event) => {
        // if (!event.target.closest(".relative.w-full")) {
        //     closeDropdown();
        // }
        const multiSelectElement = event.target.closest(
            `[data-multiselect-id="${inputId}"]`
        );

        if (!multiSelectElement && isOpen.value) {
            closeDropdown();
        }
    };

    onMounted(() => {
        document.addEventListener("click", handleClickOutside);
    });

    onUnmounted(() => {
        document.removeEventListener("click", handleClickOutside);
        clearSearchTimeout();
    });

    return {
        toggleDropdown,
        openDropdown,
        closeDropdown,
    };
}
