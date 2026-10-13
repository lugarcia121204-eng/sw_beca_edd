<template>
    <div class="flex flex-col gap-2 lg:flex-row mb-4">
        <div class="w-full md:w-1/4">
            <FormField labelFor="rows" label="Registros">
                <FormControl id="rows" placeholder="Elige un número" :options="rowsOptions"
                    :model-value="String(modelValue.rows)" @update:model-value="updateRows" :icon="mdiNumeric" />
            </FormField>
        </div>
        <div class="flex-1 w-full">
            <FormField label="Búsqueda">
                <FormControl type="search" ctrlKFocus :icon="mdiMagnify" :model-value="modelValue.search"
                    @update:model-value="updateSearch" :placeholder="searchPlaceholder" />
            </FormField>
        </div>
        <div class="flex gap-2 h-10 sm:justify-center md:basis-1/5 lg:justify-evenly lg:mt-7">
            <BaseButton @click="$emit('clear')" class="grow" :icon="mdiBroom" color="lightDark" label="Limpiar filtros"
                small />
        </div>
    </div>
</template>

<script setup>
import BaseButton from '@/components/BaseButton.vue';
import FormField from '@/components/FormField.vue';
import FormControl from '@/components/FormControl.vue';
import { mdiBroom, mdiMagnify, mdiNumeric } from '@mdi/js';

const props = defineProps({
    modelValue: {
        type: Object,
        required: true,
        default: () => ({ rows: 5, search: '' }),
    },
    rowsOptions: {
        type: Array,
        default: () => ['5', '10', '50'],
    },
    searchPlaceholder: {
        type: String,
        default: 'Ingresa un parámetro de búsqueda',
    },
});

const emit = defineEmits(['update:modelValue', 'clear']);

const updateRows = (value) => {
    emit('update:modelValue', {
        ...props.modelValue,
        rows: parseInt(value),
        page: 1
    });
};

const updateSearch = (value) => {
    emit('update:modelValue', {
        ...props.modelValue,
        search: value,
        page: 1
    });
};
</script>
