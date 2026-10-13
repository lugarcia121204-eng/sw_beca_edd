<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import LayoutAuthenticated from '@/layouts/LayoutAuthenticated.vue';
import { mdiBookOpenPageVariant, mdiFileDocumentMultiple } from '@mdi/js';
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
});

const form = useForm({
    name: '',
    description: '',
    active: false,
    archivo: null,
    via: 'ambas',
});

const archivoPreview = ref(null);
const archivoNombre = ref(null);
const archivoUrl = ref(null);
const archivoTipo = ref(null);

const viasSolicitudOptions = [
    { id: 'ambas', name: 'Ambas Vías' },
    { id: 'larga', name: 'Vía Larga (Evaluación Docente)' },
    { id: 'corta', name: 'Vía Corta (Promoción Docente)' },
];

const clearError = (field) => {
    if (form.errors[field]) {
        delete form.errors[field];
    }
};

const handleFileChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.archivo = file;
        archivoNombre.value = file.name;
        archivoTipo.value = file.type;

        // Limpiar URL anterior si existe
        if (archivoUrl.value && archivoUrl.value.startsWith('blob:')) {
            URL.revokeObjectURL(archivoUrl.value);
        }

        // Crear URL del blob para preview instantánea
        archivoUrl.value = URL.createObjectURL(file);
        archivoPreview.value = archivoUrl.value;
    }
};

const removeFile = () => {
    // Limpiar URL de blob si existe
    if (archivoUrl.value && archivoUrl.value.startsWith('blob:')) {
        URL.revokeObjectURL(archivoUrl.value);
    }

    form.archivo = null;
    archivoPreview.value = null;
    archivoNombre.value = null;
    archivoUrl.value = null;
    archivoTipo.value = null;

    const fileInput = document.getElementById('archivo-plantilla');
    if (fileInput) fileInput.value = '';
    const fileInputChange = document.getElementById('archivo-plantilla-change');
    if (fileInputChange) fileInputChange.value = '';
};

const submit = () => {
    form.clearErrors();
    
    if (!form.name) {
        form.errors.name = 'El nombre del documento es obligatorio';
        return;
    }
    
    if (!form.description) {
        form.errors.description = 'La descripción del documento es obligatoria';
        return;
    }
    
    alertaCargando('Guardando...', 'Por favor espera mientras se guarda el documento');
    form.post(route(`${props.routeName}store`), {
        onSuccess: () => {
            cerrarAlerta();
            alertaExito('¡Guardado!', 'El documento ha sido creado exitosamente');
        },
        onError: () => {
            cerrarAlerta();
            alertaError('Error', 'Hubo un problema al guardar el documento');
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
                                <path :d="mdiFileDocumentMultiple"/>
                            </svg>
                            <span class="text-gray-700 font-medium">Documentos</span>
                        </Link>
                        <svg xmlns="http://www.w3.org/2000/svg" height="12px" viewBox="0 -960 960 960" width="12px" fill="#9CA3AF">
                            <path d="m321-80-71-71 329-329-329-329 71-71 400 400L321-80Z"/>
                        </svg>
                        <span class="text-gray-900 font-semibold">Agregar Documento</span>
                    </div>
                </div>
                <Link :href="route(`${routeName}index`)" class="w-full sm:w-auto justify-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition flex items-center gap-2 font-medium cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="currentColor">
                        <path d="m313-440 224 224-57 56-320-320 320-320 57 56-224 224h487v80H313Z"/>
                    </svg>
                    Regresar
                </Link>
            </div>

            <!-- Form Card -->
            <div class="bg-white rounded-lg shadow-md border border-gray-200 p-4 sm:p-6 md:p-8">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nombre -->
                        <div class="col-span-1 md:col-span-2">
                            <label class="block mb-2 text-base text-[var(--institutional-primary)] font-medium text-gray-900">
                                Nombre del Documento: <span class="text-red-500">*</span>
                            </label>
                            <input 
                                v-model="form.name" 
                                @input="clearError('name')" 
                                type="text" 
                                class="bg-[#F3F4F6] border-t-0 border-x-0 text-gray-900 text-sm rounded-lg focus:ring-0 block w-full ps-3 p-2.5 border-b-2 border-b-gray-300 focus:border-b-[var(--institutional-primary)]" 
                                :class="{ 'border-b-red-500': form.errors.name }" 
                                placeholder="Ej. Cédula Profesional" 
                            />
                            <div v-if="!form.errors.name" class="flex items-start gap-2 mt-1 text-xs text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Introduce el nombre del documento que se requerirá</span>
                            </div>
                            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                        </div>

                        <!-- Descripción -->
                        <div class="col-span-1 md:col-span-2">
                            <label class="block mb-2 text-base text-[var(--institutional-primary)] font-medium text-gray-900">
                                Descripción del Documento: <span class="text-red-500">*</span>
                            </label>
                            <textarea 
                                v-model="form.description" 
                                @input="clearError('description')" 
                                rows="3" 
                                class="bg-[#F3F4F6] border-t-0 border-x-0 text-gray-900 text-sm rounded-lg focus:ring-0 block w-full ps-3 p-2.5 border-b-2 border-b-gray-300 focus:border-b-[var(--institutional-primary)]" 
                                :class="{ 'border-b-red-500': form.errors.description }" 
                                placeholder="Descripción del documento..."
                            ></textarea>
                            <div v-if="!form.errors.description" class="flex items-center gap-1 mt-1 text-xs text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Descripción detallada del documento</span>
                            </div>
                            <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</p>
                        </div>

                        <!-- Vía -->
                        <div class="col-span-1 md:col-span-2">
                            <label class="block mb-2 text-base text-[var(--institutional-primary)] font-medium text-gray-900">
                                Vía de Solicitud: <span class="text-red-500">*</span>
                            </label>
                            <VueSelect
                                v-model="form.via"
                                :options="viasSolicitudOptions"
                                :reduce="option => option.id"
                                label="name"
                                placeholder="Buscar o seleccionar una vía..."
                                :searchable="true"
                                :clearable="true"
                                :class="['vue-select-custom', { 'has-error': form.errors.via }]"
                                @update:modelValue="clearError('via')"
                            >
                                <template #no-options="{ search, searching }">
                                    <template v-if="searching">
                                        No se encontraron resultados para <em>{{ search }}</em>.
                                    </template>
                                    <em v-else>Comienza a escribir para buscar...</em>
                                </template>
                            </VueSelect>
                            <div v-if="!form.errors.via" class="flex items-center gap-1 mt-1 text-xs text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Selecciona para qué vía aplica este documento</span>
                            </div>
                            <p v-if="form.errors.via" class="mt-1 text-sm text-red-600">{{ form.errors.via }}</p>
                        </div>

                        <!-- Archivo de Plantilla -->
                        <div class="col-span-1 md:col-span-2">
                            <label class="block mb-2 text-base text-[var(--institutional-primary)] font-medium text-gray-900">
                                Archivo de Plantilla/Ejemplo (opcional):
                            </label>

                            <!-- Visor de archivo si existe uno seleccionado -->
                            <div v-if="archivoUrl" class="border-2 border-gray-300 rounded-lg overflow-hidden">
                                <!-- Header del archivo -->
                                <div class="bg-gray-100 border-b border-gray-300 p-3 flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">Nombre del Archivo: {{ archivoNombre }}</p>
                                        <p class="text-xs text-gray-600">Tamaño: {{ form.archivo ? (form.archivo.size / 1024 / 1024).toFixed(2) + ' MB' : '' }}</p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <label for="archivo-plantilla-change" class="px-4 py-2 bg-[var(--institutional-primary)] text-white text-sm rounded hover:bg-[#0f2347] transition cursor-pointer">
                                            Cambiar Archivo
                                        </label>
                                        <button v-if="form.archivo" type="button" @click="removeFile"
                                            class="px-4 py-2 bg-red-600 text-white text-sm rounded hover:bg-red-700 transition cursor-pointer">
                                            Cancelar
                                        </button>
                                    </div>
                                </div>

                                <!-- Visor integrado -->
                                <div class="bg-gray-100" style="height: 500px;">
                                    <iframe v-if="archivoTipo?.includes('pdf')"
                                        :src="archivoUrl"
                                        class="w-full h-full border-0"
                                        title="Visor de archivo">
                                    </iframe>
                                    <div v-else-if="archivoTipo?.startsWith('image/')" class="h-full flex items-center justify-center bg-gray-900">
                                        <img :src="archivoUrl" :alt="archivoNombre" class="max-h-full max-w-full object-contain" />
                                    </div>
                                    <div v-else class="h-full flex items-center justify-center bg-gray-50 p-6">
                                        <div class="text-center">
                                            <svg class="w-16 h-16 mx-auto text-[var(--institutional-primary)] mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            <p class="text-gray-900 font-bold mb-2 text-lg">Archivo Seleccionado</p>
                                            <p class="text-gray-600 font-medium mb-2 break-all">{{ archivoNombre }}</p>
                                            <p class="text-sm text-gray-500">Vista previa no disponible para este tipo de archivo.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Drop zone cuando NO hay archivo -->
                            <div v-else class="flex items-center justify-center w-full">
                                <label for="archivo-plantilla" class="flex flex-col items-center justify-center w-full h-48 bg-gradient-to-br from-[#F3F4F6] to-[#E5E7EB] border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:bg-gradient-to-br hover:from-[#EFF6FF] hover:to-[#DBEAFE] hover:border-[var(--institutional-primary)] transition-all">
                                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                        <svg class="w-10 h-10 mb-3 text-[var(--institutional-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                        </svg>
                                        <p class="mb-2 text-sm text-gray-700">
                                            <span class="font-semibold text-[var(--institutional-primary)]">Arrastra archivos aquí</span> o haz clic para seleccionar
                                        </p>
                                        <p class="text-xs text-gray-500">Tamaño máximo: 30MB &nbsp;|&nbsp; PDF, DOC, DOCX, XLS, XLSX, JPG, PNG</p>
                                    </div>
                                    <input id="archivo-plantilla" type="file" class="hidden" @change="handleFileChange" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png" />
                                </label>
                            </div>

                            <!-- Input oculto para cambiar archivo -->
                            <input id="archivo-plantilla-change" type="file" class="hidden" @change="handleFileChange" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png" />

                            <div class="flex items-center gap-1 mt-1 text-xs text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Sube la plantilla o ejemplo del documento requerido</span>
                            </div>
                            <p v-if="form.errors.archivo" class="mt-1 text-sm text-red-600">{{ form.errors.archivo }}</p>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center justify-end gap-3 pt-6 border-t border-gray-200">
                        <Link :href="route(`${routeName}index`)" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition cursor-pointer">
                            Cancelar
                        </Link>
                        <button 
                            :disabled="form.processing" 
                            type="submit" 
                            class="px-6 py-2 bg-[var(--institutional-primary)] text-white rounded-lg hover:bg-[#0f2347] transition shadow-lg hover:shadow-xl disabled:opacity-75 flex items-center gap-2 font-medium cursor-pointer"
                        >
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
.vue-select-custom :deep(.vs__dropdown-toggle) {
    background: linear-gradient(to bottom right, #F3F4F6, #E5E7EB);
    border: none;
    border-bottom: 2px solid #D1D5DB;
    border-radius: 0.5rem;
    padding: 0.625rem 0.75rem;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    transition: all 0.2s;
}

.vue-select-custom :deep(.vs__dropdown-toggle):hover {
    border-bottom-color: rgba(27, 57, 106, 0.5);
}
.vue-select-custom.has-error :deep(.vs__dropdown-toggle) {
    border-bottom-color: #EF4444;
}
.vue-select-custom :deep(.vs--open .vs__dropdown-toggle) {
    background: linear-gradient(to bottom right, #EFF6FF, #DBEAFE);
    border-bottom-color: #1B396A;
}

.vue-select-custom :deep(.vs__search) {
    margin: 0;
    padding: 0;
    border: none;
    font-size: 0.875rem;
    color: #111827;
}

.vue-select-custom :deep(.vs__search::placeholder) {
    color: #9CA3AF;
}

.vue-select-custom :deep(.vs__selected) {
    margin: 0;
    padding: 0;
    border: none;
    color: #111827;
    font-size: 0.875rem;
}

.vue-select-custom :deep(.vs__actions) {
    padding: 0 4px 0 6px;
}

.vue-select-custom :deep(.vs__clear),
.vue-select-custom :deep(.vs__open-indicator) {
    fill: #1B396A;
    transition: transform 0.2s;
}

.vue-select-custom :deep(.vs__open-indicator) {
    transform: scale(0.70);
}

.vue-select-custom :deep(.vs--open .vs__open-indicator) {
    transform: rotate(180deg) scale(0.70);
}

.vue-select-custom :deep(.vs__dropdown-menu) {
    border: 1px solid #E5E7EB;
    border-radius: 0.5rem;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    margin-top: 4px;
}

.vue-select-custom :deep(.vs__dropdown-option) {
    padding: 0.625rem 0.75rem;
    color: #374151;
    font-size: 0.875rem;
    transition: all 0.15s;
}

.vue-select-custom :deep(.vs__dropdown-option--highlight) {
    background: #1B396A;
    color: white;
}

.vue-select-custom :deep(.vs__no-options) {
    padding: 0.75rem;
    color: #6B7280;
    font-size: 0.875rem;
    text-align: center;
}
</style>

