<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import LayoutAuthenticated from '@/layouts/LayoutAuthenticated.vue';
import VueSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';
import { mdiBookOpenPageVariant, mdiOfficeBuilding } from '@mdi/js';
import { alertaExito, alertaError, alertaCargando, cerrarAlerta } from '@/utils/alerts.js';

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    routeName: {
        type: String,
        required: true,
    },
    institution: {
        type: Object,
        required: true,
    },
    states: {
        type: Array,
        required: true,
    },
});

const form = useForm({
    id: props.institution.data.id,
    name: props.institution.data.name,
    state_id: props.institution.data.state_id,
});

const clearError = (field) => {
    if (form.errors[field]) {
        delete form.errors[field];
    }
};

const submit = () => {
    // Limpiar errores previos
    form.clearErrors();
    
    // Validación del lado del cliente
    if (!form.name) {
        form.errors.name = 'El nombre de la institución es obligatorio';
        return;
    }
    if (!form.state_id) {
        form.errors.state_id = 'Debes seleccionar un estado';
        return;
    }
    
    alertaCargando('Actualizando...', 'Por favor espera mientras se actualiza la institución');
    form.put(route(`${props.routeName}update`, form.id), {
        onSuccess: () => {
            cerrarAlerta();
            alertaExito('¡Actualizado!', 'La institución ha sido actualizada exitosamente');
        },
        onError: () => {
            cerrarAlerta();
            alertaError('Error', 'Hubo un problema al actualizar la institución');
        }
    });
};
</script>

<template>
    <LayoutAuthenticated>
        <Head :title="title" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="w-full sm:w-auto">
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ title }}</h1>
                    <div class="flex items-center gap-2 mt-2 text-sm">
                        <svg viewBox="0 0 24 24" class="w-4 h-4 flex-shrink-0" style="fill: #1B396A;">
                            <path :d="mdiBookOpenPageVariant"/>
                        </svg>
                        <span class="text-gray-700 font-medium">Catálogo</span>
                        <svg xmlns="http://www.w3.org/2000/svg" height="12px" viewBox="0 -960 960 960" width="12px" fill="#9CA3AF">
                            <path d="m321-80-71-71 329-329-329-329 71-71 400 400L321-80Z"/>
                        </svg>
                        <Link :href="route(`${routeName}index`)" class="flex items-center gap-2 hover:underline">
                            <svg viewBox="0 0 24 24" class="w-4 h-4 flex-shrink-0" style="fill: #1B396A;">
                                <path :d="mdiOfficeBuilding"/>
                            </svg>
                             <span class="text-gray-700 font-medium">Instituciones</span>
                        </Link>
                        <svg xmlns="http://www.w3.org/2000/svg" height="12px" viewBox="0 -960 960 960" width="12px" fill="#9CA3AF">
                            <path d="m321-80-71-71 329-329-329-329 71-71 400 400L321-80Z"/>
                        </svg>
                        <span class="text-gray-900 font-semibold">Editar Institución</span>
                    </div>
                </div>
                <Link :href="route(`${routeName}index`)" class="w-full sm:w-auto justify-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition flex items-center gap-2 font-medium cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="currentColor">
                        <path d="m313-440 224 224-57 56-320-320 320-320 57 56-224 224h487v80H313Z"/>
                    </svg>
                    Regresar
                </Link>
            </div>

            <div class="bg-white rounded-lg shadow-md border border-gray-200 p-4 sm:p-6 md:p-8">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nombre -->
                        <div>
                            <label class="block mb-2 text-base text-[var(--institutional-primary)] font-medium text-gray-900">Nombre de la Institución: <span class="text-red-500">*</span></label>
                            <input v-model="form.name" @input="clearError('name')" type="text" class="bg-[#F3F4F6] border-t-0 border-x-0 text-gray-900 text-sm rounded-lg focus:ring-0 block w-full ps-3 p-2.5 border-b-2 border-b-gray-300 focus:border-b-[var(--institutional-primary)]" :class="{ 'border-b-red-500': form.errors.name }" placeholder="Nombre de la institución" />
                            <div v-if="!form.errors.name" class="flex items-center gap-1 mt-1 text-xs text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Por favor, introduce el nombre oficial de la institución</span>
                            </div>
                            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                        </div>
                        
                        <!-- Estado -->
                        <div>
                            <label class="block mb-2 text-base text-[var(--institutional-primary)] font-medium text-gray-900">Estado: <span class="text-red-500">*</span></label>
                            <VueSelect
                                v-model="form.state_id"
                                :options="states"
                                :reduce="estado => estado.id"
                                label="name"
                                placeholder="Selecciona un estado"
                                :searchable="true"
                                :clearable="true"
                                :class="['vue-select-custom', { 'vue-select-error': form.errors.state_id }]"
                                @option:selected="clearError('state_id')"
                                @option:deselected="clearError('state_id')"
                            />
                            <div class="flex items-center gap-1 mt-1 text-xs text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Selecciona el estado donde se ubica la institución</span>
                            </div>
                             <p v-if="form.errors.state_id" class="mt-1 text-sm text-red-600">{{ form.errors.state_id }}</p>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center justify-end gap-3 pt-6 border-t border-gray-200">
                        <Link :href="route(`${routeName}index`)" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition cursor-pointer">
                            Cancelar
                        </Link>
                        <button :disabled="form.processing" type="submit" class="px-6 py-2 bg-[var(--institutional-primary)] text-white rounded-lg hover:bg-[#0f2347] transition shadow-lg hover:shadow-xl disabled:opacity-75 flex items-center gap-2 font-medium cursor-pointer">
                            <span v-if="!form.processing">Actualizar</span>
                             <span v-else>Guardando...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </LayoutAuthenticated>
</template>

<style scoped>
:deep(.vue-select-custom .vs__dropdown-toggle) {
    background: #F3F4F6;
    border: none;
    border-bottom: 2px solid #d1d5db;
    border-radius: 0.5rem;
    padding: 0.625rem 0.75rem;
    min-height: 42px;
}

:deep(.vue-select-custom .vs__selected) {
    color: #111827;
    font-weight: 400;
    margin: 0;
    padding: 0;
}

:deep(.vue-select-custom .vs__search) {
    margin: 0;
    padding: 0;
    color: #111827;
}

:deep(.vue-select-custom .vs__search::placeholder) {
    color: #9ca3af;
}

:deep(.vue-select-custom .vs__dropdown-toggle:focus-within) {
    border-bottom-color: #1B396A;
}

:deep(.vue-select-custom .vs__dropdown-menu) {
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

:deep(.vue-select-custom .vs__dropdown-option) {
    padding: 0.75rem 1rem;
    color: #374151;
}

:deep(.vue-select-custom .vs__dropdown-option--highlight) {
    background: #1B396A;
    color: white;
}

:deep(.vue-select-custom .vs__open-indicator) {
    fill: #1B396A;
    transform: scale(0.85);
}

:deep(.vue-select-custom .vs__actions) {
    padding-right: 4px;
}

:deep(.vue-select-custom .vs__clear),
:deep(.vue-select-custom .vs__open-indicator) {
    fill: #1B396A;
    transition: transform 0.2s;
}

:deep(.vue-select-custom .vs__open-indicator) {
    transform: scale(0.85);
}

.vue-select-error :deep(.vs__dropdown-toggle) {
    border-bottom-color: #EF4444 !important;
}
</style>

