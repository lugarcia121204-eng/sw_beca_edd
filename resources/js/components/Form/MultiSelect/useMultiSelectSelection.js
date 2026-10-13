import { computed } from 'vue';

export function useMultiSelectSelection(props, modelValue, cachedSelectedOptions) {
    const selectedItems = computed(() => {
        if (props.asyncSearch) {
            // Para async search, combinar opciones del cache y opciones actuales
            const items = [];
            
            modelValue.value.forEach(value => {
                // Primero buscar en opciones actuales
                const currentOption = props.options.find(
                    opt => opt[props.valueKey] === value
                );
                
                if (currentOption) {
                    items.push(currentOption);
                } else if (cachedSelectedOptions.value.has(value)) {
                    // Si no está en opciones actuales, usar del cache
                    items.push(cachedSelectedOptions.value.get(value));
                }
            });
            
            return items;
        }
        
        return props.options.filter(option =>
            modelValue.value.includes(option[props.valueKey])
        );
    });

    const displayedItems = computed(() => {
        return selectedItems.value.slice(0, props.maxTags);
    });

    const remainingCount = computed(() => {
        return Math.max(0, selectedItems.value.length - props.maxTags);
    });

    const isSelected = (value) => {
        return modelValue.value.includes(value);
    };

    const toggleOption = (option, emit) => {
        if (props.disabled) return;

        const value = option[props.valueKey];
        const currentValues = [...modelValue.value];
        const index = currentValues.indexOf(value);

        if (index > -1) {
            currentValues.splice(index, 1);
            // Mantener en cache aunque se deseleccione por si vuelve a seleccionarse
        } else {
            currentValues.push(value);
            // Cachear la opción completa para async search
            if (props.asyncSearch) {
                cachedSelectedOptions.value.set(value, option);
            }
        }

        modelValue.value = currentValues;
        emit('change', currentValues);
    };

    const removeItem = (value, emit) => {
        if (props.disabled) return;

        const currentValues = modelValue.value.filter(v => v !== value);
        modelValue.value = currentValues;
        emit('change', currentValues);
    };

    const selectAll = (filteredOptions, emit) => {
        if (props.disabled) return;

        const allValues = filteredOptions.value.map(
            option => option[props.valueKey]
        );
        
        // Cachear todas las opciones seleccionadas
        if (props.asyncSearch) {
            filteredOptions.value.forEach(option => {
                cachedSelectedOptions.value.set(option[props.valueKey], option);
            });
        }
        
        const newValues = [...new Set([...modelValue.value, ...allValues])];
        modelValue.value = newValues;
        emit('change', newValues);
    };

    const clearAll = (emit) => {
        if (props.disabled) return;

        modelValue.value = [];
        emit('change', []);
    };

    return {
        selectedItems,
        displayedItems,
        remainingCount,
        isSelected,
        toggleOption,
        removeItem,
        selectAll,
        clearAll
    };
}