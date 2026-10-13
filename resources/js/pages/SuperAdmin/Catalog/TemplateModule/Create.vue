<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import LayoutAuthenticated from '@/layouts/LayoutAuthenticated.vue';
import { mdiBookOpenPageVariant, mdiFileDocumentMultiple } from '@mdi/js';
import { alertaExito, alertaError, alertaCargando, cerrarAlerta } from '@/utils/alerts.js';
import VueSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';

const props = defineProps({
    type: {
        type: String,
        default: 'recognition'
    }
});

const typeLabel = computed(() => {
    return props.type === 'recognition' ? 'Reconocimiento' : 'Carta de Aceptación';
});

const pageTitle = computed(() => 'Agregar Plantilla');
const breadcrumbTitle = computed(() => `Agregar Plantilla ${typeLabel.value}`);

const form = useForm({
    name: '',
    type: props.type,
    file: null,
});

const typeOptions = [
    { label: 'Reconocimiento (Evaluadores)', value: 'recognition' },
    { label: 'Carta de Aceptación (Docentes)', value: 'acceptance' },
];

const archivoPreview = ref(null);
const archivoNombre = ref(null);
const archivoUrl = ref(null);
const archivoTipo = ref(null);

const clearError = (field) => {
    if (form.errors[field]) {
        delete form.errors[field];
    }
};

const handleFileChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        processFile(file);
    }
};

const handleDrop = (event) => {
    const file = event.dataTransfer.files[0];
    if (file) {
        if (file.type !== 'application/pdf') {
            alertaError('Error', 'Solo se permiten archivos PDF.');
            return;
        }
        if (file.size > 10 * 1024 * 1024) {
            alertaError('Error', 'El archivo pesa más de 10MB.');
            return;
        }
        processFile(file);
    }
};

const processFile = (file) => {
    form.file = file;
    archivoNombre.value = file.name;
    archivoTipo.value = file.type;

    if (archivoUrl.value && archivoUrl.value.startsWith('blob:')) {
        URL.revokeObjectURL(archivoUrl.value);
    }

    archivoUrl.value = URL.createObjectURL(file);
    archivoPreview.value = archivoUrl.value;
};

const removeFile = () => {
    if (archivoUrl.value && archivoUrl.value.startsWith('blob:')) {
        URL.revokeObjectURL(archivoUrl.value);
    }

    form.file = null;
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
        form.errors.name = 'El nombre de la plantilla es obligatorio';
        return;
    }
    
    if (!form.file) {
        form.errors.file = 'Debes subir un archivo para la plantilla';
        return;
    }
    
    alertaCargando('Guardando...', 'Por favor espera mientras se guarda la plantilla');
    form.post(route('catalog.templates.store'), {
        onSuccess: () => {
            cerrarAlerta();
            alertaExito('¡Guardado!', 'La plantilla ha sido creada exitosamente');
        },
        onError: () => {
            cerrarAlerta();
            alertaError('Error', 'Hubo un problema al guardar la plantilla');
        }
    });
};
</script>

<template>
    <LayoutAuthenticated>
        <Head :title="pageTitle" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="w-full sm:w-auto">
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ pageTitle }}</h1>
                    <div class="flex items-center gap-2 mt-2 text-sm">
                        <svg viewBox="0 0 24 24" class="w-4 h-4 flex-shrink-0" style="fill: #1B396A;">
                            <path :d="mdiBookOpenPageVariant"/>
                        </svg>
                        <span class="text-gray-700 font-medium">Catálogo</span>
                        <svg xmlns="http://www.w3.org/2000/svg" height="12px" viewBox="0 -960 960 960" width="12px" fill="#9CA3AF">
                            <path d="m321-80-71-71 329-329-329-329 71-71 400 400L321-80Z"/>
                        </svg>
                        <Link :href="route('catalog.templates.index')" class="flex items-center gap-2 hover:underline">
                            <svg viewBox="0 0 24 24" class="w-4 h-4 flex-shrink-0" style="fill: #1B396A;">
                                <path :d="mdiFileDocumentMultiple"/>
                            </svg>
                            <span class="text-gray-700 font-medium">Plantillas</span>
                        </Link>
                        <svg xmlns="http://www.w3.org/2000/svg" height="12px" viewBox="0 -960 960 960" width="12px" fill="#9CA3AF">
                            <path d="m321-80-71-71 329-329-329-329 71-71 400 400L321-80Z"/>
                        </svg>
                        <span class="text-gray-900 font-semibold">{{ breadcrumbTitle }}</span>
                    </div>
                </div>

                <Link :href="route('catalog.templates.index')" class="w-full sm:w-auto justify-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition flex items-center gap-2 font-medium cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="currentColor">
                        <path d="m313-440 224 224-57 56-320-320 320-320 57 56-224 224h487v80H313Z"/>
                    </svg>
                    Regresar
                </Link>
            </div>

            <!-- Form Card -->
            <div class="bg-white rounded-xl shadow-md border border-gray-200 p-8">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nombre -->
                        <div class="col-span-1 md:col-span-2">
                            <label class="block mb-2 text-base text-[var(--institutional-primary)] font-medium text-gray-900">
                                Nombre de la Plantilla: <span class="text-red-500">*</span>
                            </label>
                            <input 
                                v-model="form.name" 
                                @input="clearError('name')" 
                                type="text" 
                                class="bg-[#F3F4F6] border-t-0 border-x-0 text-gray-900 text-sm rounded-lg focus:ring-0 block w-full ps-3 p-2.5 border-b-2 border-b-gray-300 focus:border-b-[var(--institutional-primary)]" 
                                :class="{ 'border-b-red-500': form.errors.name }" 
                                placeholder="Ej. Formato Oficial 2026" 
                            />
                            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                        </div>

                        <!-- Hidden Type Input -->
                        <input type="hidden" v-model="form.type">

                        <!-- Archivo de Plantilla -->
                        <div class="col-span-1 md:col-span-2">
                            <label class="block mb-2 text-base text-[var(--institutional-primary)] font-medium text-gray-900">
                                Archivo de Plantilla (PDF): <span class="text-red-500">*</span>
                            </label>

                            <!-- Visor de archivo si existe uno seleccionado -->
                            <div v-if="archivoUrl" class="border-2 border-gray-300 rounded-lg overflow-hidden">
                                <!-- Header del archivo -->
                                <div class="bg-gray-100 border-b border-gray-300 p-4 flex items-center justify-between flex-wrap gap-2">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900 truncate max-w-md">
                                            {{ archivoNombre }}
                                        </p>
                                        <p class="text-xs text-gray-600">
                                            Tamaño: {{ (form.file.size / 1024 / 1024).toFixed(2) }} MB
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <label for="archivo-plantilla-change" class="cursor-pointer px-4 py-2 bg-[var(--institutional-primary)] text-white text-sm rounded hover:bg-[#0f2347] transition shadow-sm font-medium">
                                            Cambiar Archivo
                                            <input id="archivo-plantilla-change" type="file" class="hidden" @change="handleFileChange" accept=".pdf" />
                                        </label>
                                        <button type="button" @click="removeFile"
                                            class="px-4 py-2 bg-red-600 text-white text-sm rounded hover:bg-red-700 transition shadow-sm font-medium cursor-pointer">
                                            Eliminar
                                        </button>
                                    </div>
                                </div>

                                <!-- Visor integrado -->
                                <div class="bg-gray-100 h-[500px]">
                                    <iframe v-if="archivoTipo?.includes('pdf')"
                                        :src="archivoUrl"
                                        class="w-full h-full border-0"
                                        title="Visor de archivo">
                                    </iframe>
                                    <div v-else class="h-full flex items-center justify-center bg-gray-50 p-6">
                                        <div class="text-center">
                                            <svg class="w-16 h-16 mx-auto text-[var(--institutional-primary)] mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            <p class="text-gray-900 font-bold mb-2 text-lg">Archivo Cargado</p>
                                            <p class="text-gray-600 font-medium mb-2">Vista previa no disponible</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Drop zone cuando NO hay archivo -->
                            <div v-else class="flex items-center justify-center w-full">
                                <label 
                                    for="archivo-plantilla" 
                                    class="flex flex-col items-center justify-center w-full h-48 bg-gradient-to-br from-[#F3F4F6] to-[#E5E7EB] border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:bg-gradient-to-br hover:from-[#EFF6FF] hover:to-[#DBEAFE] hover:border-[var(--institutional-primary)] transition-all relative"
                                    @dragover.prevent
                                    @drop.prevent="handleDrop"
                                >
                                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                        <svg class="w-10 h-10 mb-3 text-[var(--institutional-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                        </svg>
                                        <p class="mb-2 text-sm text-gray-700">
                                            <span class="font-semibold text-[var(--institutional-primary)]">Arrastra archivos aquí</span> o haz clic para seleccionar
                                        </p>
                                        <p class="text-xs text-gray-500">Formato PDF (Máx. 10MB)</p>
                                    </div>
                                    <input id="archivo-plantilla" type="file" class="hidden" @change="handleFileChange" accept=".pdf" />
                                </label>
                            </div>
 

                            <!-- Input oculto para cambiar archivo -->
                            <input id="archivo-plantilla-change" type="file" class="hidden" @change="handleFileChange" accept=".pdf" />
                            
                            <p v-if="form.errors.file" class="mt-1 text-sm text-red-600">{{ form.errors.file }}</p>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center justify-end gap-3 pt-6 border-t border-gray-200">
                        <Link :href="route('catalog.templates.index')" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition cursor-pointer">
                            Cancelar
                        </Link>
                        <button 
                            :disabled="form.processing" 
                            type="submit" 
                            class="px-6 py-2 bg-[var(--institutional-primary)] text-white rounded-lg hover:bg-[#0f2347] transition shadow-lg hover:shadow-xl disabled:opacity-75 flex items-center gap-2 font-medium cursor-pointer"
                        >
                            <span v-if="!form.processing">Guardar Plantilla</span>
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
    background: linear-gradient(to bottom, #F3F4F6 0%, #F3F4F6 100%); /* Matches inputs */
    border: none;
    border-bottom: 2px solid #d1d5db;
    border-radius: 0.5rem 0.5rem 0 0; /* Only top rounded */
    padding: 0.5rem;
    min-height: 42px;
}

:deep(.vue-select-custom .vs__dropdown-toggle:focus-within) {
    border-color: #1B396A;
}

:deep(.vue-select-custom .vs__selected) {
    color: #111827; /* gray-900 */
}

:deep(.vue-select-custom .vs__search::placeholder) {
    color: #9ca3af;
}
</style>

