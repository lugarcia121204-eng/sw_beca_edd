<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import LayoutAuthenticated from '@/layouts/LayoutAuthenticated.vue';
import { mdiSecurity, mdiLockCheckOutline } from '@mdi/js';
import VueSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';
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
    modules: {
        type: Array,
        required: true,
    },
});

const form = useForm({
    name: '',
    guard_name: 'web',
    description: '',
    module_key: '', 
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
    if (!form.module_key) {
        form.errors.module_key = 'Debes seleccionar un módulo';
        return;
    }
    if (!form.name) {
        form.errors.name = 'La clave es obligatorio';
        return;
    }
    if (!form.description) {
        form.errors.description = 'La descripción es obligatoria';
        return;
    }
    
    // Si todo está correcto, mostrar alerta de cargando y enviar
    alertaCargando('Guardando', 'Por favor espera...');
    
    form.post(route(`${props.routeName}store`), {
        onSuccess: () => {
            cerrarAlerta();
            alertaExito('¡Éxito!', 'Permiso creado correctamente');
        },
        onError: () => {
            cerrarAlerta();
            alertaError('Error', 'Por favor verifica los datos ingresados');
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
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ title }}</h1>
                    <div class="flex items-center gap-2 mt-2 text-sm">
                        <svg viewBox="0 0 24 24" class="w-4 h-4 flex-shrink-0" style="fill: #1B396A;">
                            <path :d="mdiSecurity"/>
                        </svg>
                        <span class="text-gray-700 font-medium">Seguridad</span>
                        <svg xmlns="http://www.w3.org/2000/svg" height="12px" viewBox="0 -960 960 960" width="12px" fill="#9CA3AF">
                            <path d="m321-80-71-71 329-329-329-329 71-71 400 400L321-80Z"/>
                        </svg>
                        <Link :href="route(`${routeName}index`)" class="flex items-center gap-2 hover:underline">
                            <svg viewBox="0 0 24 24" class="w-4 h-4 flex-shrink-0" style="fill: #1B396A;">
                                <path :d="mdiLockCheckOutline"/>
                            </svg>
                            <span class="text-gray-700 font-medium">Permisos</span>
                        </Link>
                        <svg xmlns="http://www.w3.org/2000/svg" height="12px" viewBox="0 -960 960 960" width="12px" fill="#9CA3AF">
                            <path d="m321-80-71-71 329-329-329-329 71-71 400 400L321-80Z"/>
                        </svg>
                        <span class="text-gray-900 font-semibold">Agregar Permiso</span>
                    </div>
                </div>
                <Link :href="route(`${routeName}index`)" class="w-full sm:w-auto justify-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition flex items-center gap-2 font-medium">
                    <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="currentColor">
                        <path d="m313-440 224 224-57 56-320-320 320-320 57 56-224 224h487v80H313Z"/>
                    </svg>
                    Regresar
                </Link>
            </div>

            <div class="bg-white rounded-lg shadow-md border border-gray-200 p-4 sm:p-6 md:p-8">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Module Selector -->
                        <div>
                            <label class="block mb-2 text-base text-[var(--institutional-primary)] font-medium text-gray-900">Módulo: <span class="text-red-500">*</span></label>
                            <VueSelect
                                v-model="form.module_key"
                                :options="modules.map(m => ({ label: m.name, value: m.key }))"
                                :reduce="option => option.value"
                                placeholder="Seleccione una Opción"
                                :searchable="true"
                                :clearable="true"
                                :class="['vue-select-custom', { 'vue-select-error': form.errors.module_key }]"
                                @option:selected="clearError('module_key')"
                                @option:deselected="clearError('module_key')"
                            />
                            <div v-if="!form.errors.module_key" class="flex items-center gap-1 mt-1 text-xs text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Selecciona un módulo disponible</span>
                            </div>
                            <p v-if="form.errors.module_key" class="mt-1 text-sm text-red-600">{{ form.errors.module_key }}</p>
                        </div>
                        
                         <!-- Permission Name (Key) -->
                        <div>
                            <label class="block mb-2 text-base text-[var(--institutional-primary)] font-medium text-gray-900">Clave: <span class="text-red-500">*</span></label>
                            <input v-model="form.name" type="text" class="bg-[#F3F4F6] border-t-0 border-x-0 text-gray-900 text-sm rounded-lg focus:ring-0 block w-full ps-3 p-2.5 border-b-2 border-b-gray-300 focus:border-b-[var(--institutional-primary)]" :class="{ 'border-b-red-500': form.errors.name }" placeholder="Clave del permiso" @input="clearError('name')" />
                            <div v-if="!form.errors.name" class="flex items-center gap-1 mt-1 text-xs text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Ejemplos: nombre_modulo.index, nombre_modulo.store, ...</span>
                            </div>
                            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                        </div>

                         <!-- Description -->
                        <div class="col-span-1 md:col-span-2">
                            <label class="block mb-2 text-base text-[var(--institutional-primary)] font-medium text-gray-900">Descripción: <span class="text-red-500">*</span></label>
                            <textarea v-model="form.description" rows="4" class="bg-[#F3F4F6] border-t-0 border-x-0 text-gray-900 text-sm rounded-lg focus:ring-0 block w-full ps-3 p-2.5 border-b-2 border-b-gray-300 focus:border-b-[var(--institutional-primary)]" :class="{ 'border-b-red-500': form.errors.description }" placeholder="Descripción" @input="clearError('description')"></textarea>
                            <div class="flex items-center justify-between mt-1">
                                <div v-if="!form.errors.description" class="flex items-center gap-1 text-xs text-gray-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>Ejemplos: index=Leer Registros, store=Crear Registros...</span>
                                </div>
                                <div v-if="form.errors.description" class="text-sm text-red-600">
                                    {{ form.errors.description }}
                                </div>
                                <span class="text-gray-400 text-sm">{{ form.description?.length || 0 }}/255</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center justify-end gap-3 pt-6 border-t border-gray-200">
                        <Link :href="route(`${routeName}index`)" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition">
                            Cancelar
                        </Link>
                        <button :disabled="form.processing" type="submit" class="px-6 py-2 cursor-pointer bg-[var(--institutional-primary)] text-white rounded-lg hover:bg-[#0f2347] transition shadow-lg hover:shadow-xl disabled:opacity-75 flex items-center gap-2 font-medium">
                            <span v-if="!form.processing">Guardar</span>
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
    background: linear-gradient(to bottom right, #F3F4F6, #E5E7EB);
    border: none;
    border-bottom: 2px solid #D1D5DB;
    border-radius: 0.5rem;
    padding: 0.625rem 0.75rem;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    transition: all 0.2s;
}

:deep(.vue-select-custom .vs__dropdown-toggle):hover {
    border-bottom-color: rgba(27, 57, 106, 0.5);
}

:deep(.vue-select-custom .vs--open .vs__dropdown-toggle) {
    background: linear-gradient(to bottom right, #EFF6FF, #DBEAFE);
    border-bottom-color: #1B396A;
}

:deep(.vue-select-custom .vs__search) {
    margin: 0;
    padding: 0;
    border: none;
    font-size: 0.875rem;
    color: #111827;
}

:deep(.vue-select-custom .vs__search::placeholder) {
    color: #9CA3AF;
}

:deep(.vue-select-custom .vs__selected) {
    margin: 0;
    padding: 0;
    border: none;
    color: #111827;
    font-size: 0.875rem;
}

:deep(.vue-select-custom .vs__actions) {
    padding: 0 4px 0 6px;
}

:deep(.vue-select-custom .vs__clear),
:deep(.vue-select-custom .vs__open-indicator) {
    fill: #1B396A;
    transition: transform 0.2s;
}

:deep(.vue-select-custom .vs__open-indicator) {
    transform: scale(0.70);
}

:deep(.vue-select-custom .vs--open .vs__open-indicator) {
    transform: rotate(180deg) scale(0.70);
}

/* Error state */
.vue-select-error :deep(.vs__dropdown-toggle),
.vue-select-error :deep(.vs--open .vs__dropdown-toggle),
.vue-select-error :deep(.vs__dropdown-toggle):hover {
    border-bottom-color: #EF4444 !important;
}

:deep(.vue-select-custom .vs__dropdown-menu) {
    border: 1px solid #E5E7EB;
    border-radius: 0.5rem;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    margin-top: 4px;
}

:deep(.vue-select-custom .vs__dropdown-option) {
    padding: 0.625rem 0.75rem;
    color: #374151;
    font-size: 0.875rem;
    transition: all 0.15s;
}

:deep(.vue-select-custom .vs__dropdown-option--highlight) {
    background: #1B396A;
    color: white;
}

:deep(.vue-select-custom .vs__no-options) {
    padding: 0.75rem;
    color: #6B7280;
    font-size: 0.875rem;
    text-align: center;
}
</style>
