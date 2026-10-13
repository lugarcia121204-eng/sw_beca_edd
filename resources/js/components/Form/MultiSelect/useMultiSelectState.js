import { ref, computed, onMounted } from "vue";

const cachedSelectedOptions = ref(new Map());

export function useMultiSelectState(props) {
    const isOpen = ref(false);
    const searchTerm = ref("");
    const searchInput = ref(null);
    const isLoading = ref(false);
    const inputId = `multi-select-${Math.random().toString(36).substr(2, 9)}`;

    const hasError = computed(() => {
        return Boolean(props.errorMessage);
    });

    const initializeCache = () => {
        if (!props.asyncSearch) return;

        if (
            props.initialSelectedOptions &&
            props.initialSelectedOptions.length > 0
        ) {
            props.initialSelectedOptions.forEach((option) => {
                cachedSelectedOptions.value.set(option[props.valueKey], option);
            });
        }

        if (props.options.length > 0 && modelValue.value.length > 0) {
            props.options.forEach((option) => {
                if (modelValue.value.includes(option[props.valueKey])) {
                    cachedSelectedOptions.value.set(
                        option[props.valueKey],
                        option
                    );
                }
            });
        }
    };

    onMounted(() => {
        initializeCache();
    });

    return {
        isOpen,
        searchTerm,
        searchInput,
        isLoading,
        cachedSelectedOptions,
        inputId,
        hasError,
    };
}
