
import { watch } from 'vue';

export function useMultiSelectValidation(props, modelValue, isLoading, emit) {
    watch(
        () => props.options,
        () => {
            // Validar selección actual contra nuevas opciones
            if (modelValue.value.length > 0 && !props.asyncSearch) {
                const validValues = props.options.map(
                    opt => opt[props.valueKey]
                );
                const filteredValues = modelValue.value.filter(val =>
                    validValues.includes(val)
                );

                if (filteredValues.length !== modelValue.value.length) {
                    modelValue.value = filteredValues;
                    emit('change', filteredValues);
                }
            }

            // Desactivar loading cuando lleguen nuevas opciones
            if (props.asyncSearch) {
                isLoading.value = false;
            }
        },
        { deep: true }
    );
}