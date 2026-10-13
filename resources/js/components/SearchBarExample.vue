<template>
    <CardBox class="mb-6">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-2">
                <BaseIcon :path="mdiFilter" size="24" h="h-auto" w="w-auto" />
                <h2 class="text-lg font-semibold text-gray-800">Filtros de Búsqueda</h2>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-600">6 resultados</span>
                <div class="w-2 h-2 bg-green-500 rounded-full"></div>
            </div>
        </div>

        <div class="md:flex gap-2 items-center">
            <FormField label="Registros">
                <FormControl v-model="filters.rows" :options="rows" />
            </FormField>
            <FormField class="w-full" label="Búsqueda">
                <FormControl v-model="filters.search" :icon="mdiMagnify" />
            </FormField>
            <BaseButton label="Limpiar" color="lightDark" :small="false" :icon="mdiBroom" />
            <BaseButton v-if="routeName" label="Agregar" color="info" :small="false" :icon="mdiPlus" :route-name="routeName" />
        </div>
    </CardBox>
</template>
<script setup>
import BaseButton from '@/components/BaseButton.vue';
import BaseIcon from '@/components/BaseIcon.vue';
import CardBox from '@/components/CardBox.vue';
import FormControl from '@/components/FormControl.vue';
import FormField from '@/components/FormField.vue';
import { mdiBroom, mdiFilter, mdiMagnify, mdiPlus } from '@mdi/js';
import { reactive } from 'vue';

defineProps({
    routeName: String,
})

const filters = reactive({
    rows: 5,
    search: null,
})

const rows = [5, 10, 25, 50]
</script>
