<script setup>
import { ref, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import LayoutAuthenticated from '@/layouts/LayoutAuthenticated.vue';
import { mdiBullhorn } from '@mdi/js';
import VueSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';
import { alertaExito, alertaError, alertaCargando, cerrarAlerta } from '@/utils/alerts.js';
import AnnouncementCalendar from '@/components/AnnouncementCalendar.vue';

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    routeName: {
        type: String,
        required: true,
    },
    announcement: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    id: props.announcement.data?.id || props.announcement.id,
    name: props.announcement.data?.name || props.announcement.name,
    description: props.announcement.data?.description || props.announcement.description,
    status: props.announcement.data?.status || props.announcement.status,
    file: null,
    image: null,
    _method: 'PUT',
    // Calendar Fields (Handle potential data wrapper from Resource)
    // Calendar Fields (Handle potential data wrapper from Resource)
    publication_start: getCalendarDate(props.announcement, 'publication_start'),
    registration_start: getCalendarDate(props.announcement, 'registration_start'),
    registration_end: getCalendarDate(props.announcement, 'registration_end'),
    evaluation_start: getCalendarDate(props.announcement, 'evaluation_start'),
    evaluation_end: getCalendarDate(props.announcement, 'evaluation_end'),
    results_start: getCalendarDate(props.announcement, 'results_start'),
    results_end: getCalendarDate(props.announcement, 'results_end'),
});

function getCalendarDate(announcement, field) {
    const calendar = announcement.data?.calendar || announcement.calendar;
    if (!calendar) return '';
    // Check if wrapped in data
    if (calendar.data && calendar.data[field]) return calendar.data[field];
    // Check if direct property
    if (calendar[field]) return calendar[field];
    return '';
}


const archivoPreview = ref(null);
const savedAnnouncement = props.announcement.data || props.announcement;
const savedFileUrl = savedAnnouncement.file_path
    ? route('announcements.preview', savedAnnouncement.id)
    : null;
const savedImageUrl = savedAnnouncement.image_path
    ? route('announcements.preview', { announcement: savedAnnouncement.id, type: 'image' })
    : null;

const archivoNombre = ref(props.announcement.data?.file_name || null);
const archivoActual = ref(props.announcement.data?.file_path || null);
const archivoUrl = ref(savedFileUrl);
const archivoTipo = ref(props.announcement.data?.file_type || null);

const imagenPreview = ref(null);
const imagenNombre = ref(null);
const imagenActual = ref(props.announcement.data?.image_path || null);
const imagenUrl = ref(savedImageUrl);

const statusOptions = [
    { id: 'pendiente', label: 'Pendiente' },
    { id: 'activa', label: 'Activa' },
    { id: 'cerrada', label: 'Cerrada' }
];

// ref bridge: v-model on a component requires ref (not reactive) so the full object can be replaced
const calendarDates = ref({
    publication_start:  form.publication_start,
    registration_start: form.registration_start,
    registration_end:   form.registration_end,
    evaluation_start:   form.evaluation_start,
    evaluation_end:     form.evaluation_end,
    results_start:      form.results_start,
    results_end:        form.results_end,
});

watch(calendarDates, (v) => {
    form.publication_start  = v.publication_start;
    form.registration_start = v.registration_start;
    form.registration_end   = v.registration_end;
    form.evaluation_start   = v.evaluation_start;
    form.evaluation_end     = v.evaluation_end;
    form.results_start      = v.results_start;
    form.results_end        = v.results_end;
}, { deep: true });

const handleFileChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.file = file;
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

const handleImageChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.image = file;
        imagenNombre.value = file.name;
        
        // Limpiar URL anterior si existe
        if (imagenUrl.value && imagenUrl.value.startsWith('blob:')) {
            URL.revokeObjectURL(imagenUrl.value);
        }
        
        // Crear URL del blob para preview instantánea
        imagenUrl.value = URL.createObjectURL(file);
        imagenPreview.value = imagenUrl.value;
    }
};

const removeFile = () => {
    // Limpiar URL de blob si existe
    if (archivoUrl.value && archivoUrl.value.startsWith('blob:')) {
        URL.revokeObjectURL(archivoUrl.value);
    }
    
    form.file = null;
    archivoPreview.value = null;
    
    // Restaurar archivo original si existe
    if (archivoActual.value) {
        archivoUrl.value = savedFileUrl;
        archivoNombre.value = props.announcement.data?.file_name || null;
        archivoTipo.value = props.announcement.data?.file_type || null;
    } else {
        archivoUrl.value = null;
        archivoNombre.value = null;
        archivoTipo.value = null;
    }
};

const removeImage = () => {
    // Limpiar URL de blob si existe
    if (imagenUrl.value && imagenUrl.value.startsWith('blob:')) {
        URL.revokeObjectURL(imagenUrl.value);
    }
    
    form.image = null;
    imagenPreview.value = null;
    
    // Restaurar imagen original si existe
    if (imagenActual.value) {
        imagenUrl.value = savedImageUrl;
        imagenNombre.value = null; 
    } else {
        imagenUrl.value = null;
        imagenNombre.value = null;
    }
};

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
        form.errors.name = 'El nombre es obligatorio';
        return;
    }
    if (!form.status) {
        form.errors.status = 'El estado es obligatorio';
        return;
    }
    if (!form.publication_start) {
        form.errors.publication_start = 'La fecha de publicación es obligatoria';
        return;
    }
    if (!form.registration_start || !form.registration_end) {
        if (!form.registration_start) form.errors.registration_start = 'La fecha de inicio de registro es obligatoria';
        if (!form.registration_end) form.errors.registration_end = 'La fecha de fin de registro es obligatoria';
        return;
    }
    if (!form.evaluation_start || !form.evaluation_end) {
        if (!form.evaluation_start) form.errors.evaluation_start = 'La fecha de inicio de evaluación es obligatoria';
        if (!form.evaluation_end) form.errors.evaluation_end = 'La fecha de fin de evaluación es obligatoria';
        return;
    }
    if (!form.results_start || !form.results_end) {
        if (!form.results_start) form.errors.results_start = 'La fecha de inicio de resultados es obligatoria';
        if (!form.results_end) form.errors.results_end = 'La fecha de fin de resultados es obligatoria';
        return;
    }
    
    // Si todo está correcto, mostrar alerta de cargando y enviar
    alertaCargando('Actualizando', 'Por favor espera...');
    
    form.post(route(`${props.routeName}update`, { announcement: form.id }), {
        onSuccess: () => {
            cerrarAlerta();
            alertaExito('¡Éxito!', 'Convocatoria actualizada correctamente');
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
                        <Link :href="route(`${routeName}index`)" class="flex items-center gap-2 hover:underline">
                            <svg viewBox="0 0 24 24" class="w-4 h-4 flex-shrink-0" style="fill: #1B396A;">
                                <path :d="mdiBullhorn"/>
                            </svg>
                            <span class="text-gray-700 font-medium">Convocatorias</span>
                        </Link>
                        <svg xmlns="http://www.w3.org/2000/svg" height="12px" viewBox="0 -960 960 960" width="12px" fill="#9CA3AF">
                            <path d="m321-80-71-71 329-329-329-329 71-71 400 400L321-80Z"/>
                        </svg>
                        <span class="text-gray-900 font-semibold">Editar Convocatoria</span>
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
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                        <!-- Nombre -->
                        <div>
                            <label class="block mb-2 text-base text-[var(--institutional-primary)] font-medium text-gray-900">Nombre: <span class="text-red-500">*</span></label>
                            <input v-model="form.name" type="text" class="bg-[#F3F4F6] border-t-0 border-x-0 text-gray-900 text-sm rounded-lg focus:ring-0 block w-full ps-3 p-2.5 border-b-2 border-b-gray-300 focus:border-b-[var(--institutional-primary)]" :class="{ 'border-b-red-500': form.errors.name }" placeholder="Nombre de la convocatoria" @input="clearError('name')" />
                            <div v-if="!form.errors.name" class="flex items-center gap-1 mt-1 text-xs text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Por favor, introduce el nombre de la convocatoria</span>
                            </div>
                            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                        </div>
                        
                        <!-- Estado -->
                        <div>
                            <label class="block mb-2 text-base text-[var(--institutional-primary)] font-medium text-gray-900">Estado: <span class="text-red-500">*</span></label>
                            <VueSelect
                                v-model="form.status"
                                :options="statusOptions"
                                :reduce="option => option.id"
                                label="label"
                                placeholder="Selecciona un estado..."
                                :searchable="false"
                                :clearable="false"
                                class="vue-select-custom"
                                @update:modelValue="clearError('status')"
                            />
                            <div v-if="!form.errors.status" class="flex items-center gap-1 mt-1 text-xs text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Por favor, selecciona el estado de la convocatoria</span>
                            </div>
                            <p v-if="form.errors.status" class="mt-1 text-sm text-red-600">{{ form.errors.status }}</p>
                        </div>

                        <!-- Descripción -->
                        <div class="col-span-1 md:col-span-2">
                            <label class="block mb-2 text-base text-[var(--institutional-primary)] font-medium text-gray-900">Descripción:</label>
                            <textarea v-model="form.description" rows="4" maxlength="500" class="bg-[#F3F4F6] border-t-0 border-x-0 text-gray-900 text-sm rounded-lg focus:ring-0 block w-full ps-3 p-2.5 border-b-2 border-b-gray-300 focus:border-b-[var(--institutional-primary)]" :class="{ 'border-b-red-500': form.errors.description }" placeholder="Descripción de la convocatoria" @input="clearError('description')"></textarea>
                            <div class="flex items-center justify-between mt-1">
                                <div v-if="!form.errors.description" class="flex items-center gap-1 text-xs text-gray-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>Por favor, introduce la descripción de la convocatoria</span>
                                </div>
                                <div v-if="form.errors.description" class="text-sm text-red-600">
                                    {{ form.errors.description }}
                                </div>
                                <span class="text-gray-400 text-sm">{{ form.description?.length || 0 }}/500</span>
                            </div>
                        </div>

                        <!-- Imagen de Convocatoria -->
                        <div class="col-span-1 md:col-span-2">
                            <label class="block mb-2 text-base text-[var(--institutional-primary)] font-medium text-gray-900">
                                Imagen de Portada:
                            </label>
                            
                            <!-- Visor de imagen cuando hay una seleccionada -->
                            <div v-if="imagenUrl" class="border-2 border-gray-300 rounded-lg overflow-hidden bg-gray-50 flex flex-col items-center p-4">
                                <img :src="imagenUrl" :alt="imagenNombre" class="max-h-64 object-contain shadow-md rounded-lg mb-4" />
                                <div class="flex items-center gap-3">
                                    <span v-if="imagenNombre" class="text-sm font-semibold text-gray-700">{{ imagenNombre }}</span>
                                    <div class="flex items-center gap-2">
                                        <label for="imagen-convocatoria-change" class="px-3 py-1 bg-[var(--institutional-primary)] text-white text-xs rounded hover:bg-[#0f2347] transition cursor-pointer">
                                            Cambiar Imagen
                                        </label>
                                        <button v-if="form.image" type="button" @click="removeImage" 
                                            class="px-3 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700 transition cursor-pointer">
                                            Revertir
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Drop zone cuando NO hay imagen -->
                            <div v-else class="flex items-center justify-center w-full">
                                <label for="imagen-convocatoria" class="flex flex-col items-center justify-center w-full h-32 bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:bg-gray-100 hover:border-[var(--institutional-primary)] transition-all">
                                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                        <svg class="w-8 h-8 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <p class="mb-2 text-sm text-gray-500">
                                            <span class="font-semibold text-[var(--institutional-primary)]">Sube una imagen</span> o arrastra aquí
                                        </p>
                                        <p class="text-xs text-gray-500">PNG, JPG o GIF up to 2MB</p>
                                    </div>
                                    <input id="imagen-convocatoria" type="file" class="hidden" @change="handleImageChange" accept="image/*" />
                                </label>
                            </div>
                            
                            <!-- Input para cambiar imagen (oculto) -->
                            <input id="imagen-convocatoria-change" type="file" class="hidden" @change="handleImageChange" accept="image/*" />

                            <p v-if="form.errors.image" class="mt-1 text-sm text-red-600">{{ form.errors.image }}</p>
                        </div>

                        <!-- Archivo de Convocatoria -->
                        <div class="col-span-1 md:col-span-2">
                            <label class="block mb-2 text-base text-[var(--institutional-primary)] font-medium text-gray-900">
                                Archivo de Convocatoria:
                            </label>
                            
                            <!-- Visor de archivo si existe uno (actual o nuevo) -->
                            <div v-if="archivoUrl" class="border-2 border-gray-300 rounded-lg overflow-hidden">
                                <!-- Header del archivo -->
                                <div class="bg-gray-100 border-b border-gray-300 p-3 flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">Nombre del Archivo: {{ archivoNombre }}</p>
                                        <p class="text-xs text-gray-600">Tamaño: {{ form.file ? (form.file.size / 1024 / 1024).toFixed(2) + ' MB' : '2.5 MB' }}</p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <label for="archivo-convocatoria-change" class="px-4 py-2 bg-[var(--institutional-primary)] text-white text-sm rounded hover:bg-[#0f2347] transition cursor-pointer">
                                            Cambiar Archivo
                                        </label>
                                        <button v-if="form.file" type="button" @click="removeFile" 
                                            class="px-4 py-2 bg-red-600 text-white text-sm rounded hover:bg-red-700 transition cursor-pointer">
                                            Cancelar
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Visor integrado -->
                                <div class="bg-gray-100 h-[55vh] sm:h-[65vh] lg:h-[500px]">
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
                                <label for="archivo-convocatoria" class="flex flex-col items-center justify-center w-full h-48 bg-gradient-to-br from-[#F3F4F6] to-[#E5E7EB] border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:bg-gradient-to-br hover:from-[#EFF6FF] hover:to-[#DBEAFE] hover:border-[var(--institutional-primary)] transition-all">
                                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                        <svg class="w-10 h-10 mb-3 text-[var(--institutional-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                        </svg>
                                        <p class="mb-2 text-sm text-gray-700">
                                            <span class="font-semibold text-[var(--institutional-primary)]">Arrastra archivos aquí</span> o haz clic para seleccionar
                                        </p>
                                        <p class="text-xs text-gray-500">Tamaño máximo: 30MB Admite: JPG, PNG, PDF</p>
                                    </div>
                                    <input id="archivo-convocatoria" type="file" class="hidden" @change="handleFileChange" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" />
                                </label>
                            </div>

                            <!-- Inputs ocultos para cambiar archivo -->
                            <input id="archivo-convocatoria" type="file" class="hidden" @change="handleFileChange" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" />
                            <input id="archivo-convocatoria-change" type="file" class="hidden" @change="handleFileChange" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" />

                            <div class="flex items-center gap-1 mt-1 text-xs text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Sube el documento oficial de la convocatoria</span>
                            </div>
                            <p v-if="form.errors.file" class="mt-1 text-sm text-red-600">{{ form.errors.file }}</p>
                        </div>
                    </div>

                    <!-- Calendario Section -->
                    <div>
                        <div class="flex items-center gap-2 mb-6">
                            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#1B396A">
                                <path d="M200-80q-33 0-56.5-23.5T120-160v-560q0-33 23.5-56.5T200-800h40v-80h80v80h320v-80h80v80h40q33 0 56.5 23.5T840-720v560q0 33-23.5 56.5T760-80H200Zm0-80h560v-400H200v400Zm0-480h560v-80H200v80Zm0 0v-80 80Z"/>
                            </svg>
                            <h2 class="text-xl font-bold text-gray-900">Calendario de la Convocatoria</h2>
                        </div>

                        <AnnouncementCalendar v-model="calendarDates" :is-edit="true" />

                        <!-- Validation errors -->
                        <div class="mt-4 space-y-1">
                            <p v-if="form.errors.publication_start" class="text-sm text-red-600">Publicación: {{ form.errors.publication_start }}</p>
                            <p v-if="form.errors.registration_start" class="text-sm text-red-600">Registro inicio: {{ form.errors.registration_start }}</p>
                            <p v-if="form.errors.registration_end" class="text-sm text-red-600">Registro fin: {{ form.errors.registration_end }}</p>
                            <p v-if="form.errors.evaluation_start" class="text-sm text-red-600">Evaluación inicio: {{ form.errors.evaluation_start }}</p>
                            <p v-if="form.errors.evaluation_end" class="text-sm text-red-600">Evaluación fin: {{ form.errors.evaluation_end }}</p>
                            <p v-if="form.errors.results_start" class="text-sm text-red-600">Resultados inicio: {{ form.errors.results_start }}</p>
                            <p v-if="form.errors.results_end" class="text-sm text-red-600">Resultados fin: {{ form.errors.results_end }}</p>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center justify-end gap-3 pt-6 border-t border-gray-200">
                        <Link :href="route(`${routeName}index`)" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition">
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

