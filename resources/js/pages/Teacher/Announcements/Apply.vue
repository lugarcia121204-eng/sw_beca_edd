<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import TeacherLayout from '@/layouts/TeacherLayout.vue';
import TextInput from '@/components/TextInput.vue';
import InputLabel from '@/components/InputLabel.vue';
import InputError from '@/components/InputError.vue';
import { mdiArrowLeft, mdiFileDocumentOutline, mdiCloudUpload, mdiCheckBold, mdiEye, mdiEyeOff, mdiRefresh, mdiBullhorn, mdiFilePlus } from '@mdi/js';
import { ref, onMounted, computed, watch } from 'vue';
import { alertaCargando, cerrarAlerta, alertaError, alertaConfirmacionEscrita, alertaPregunta } from '@/utils/alerts.js';
import VueSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';

const props = defineProps({
    announcement: Object,
    catalog_documents: {
        type: Array,
        default: () => [],
    },
    previous_documents: {
        type: Array,
        default: () => [],
    },
    position_types: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    announcement_id: props.announcement.id,
    position_type_id: '', 
    files: [], 
    file_types: [], 
    reused_documents: {}, 
    via: 'larga',
});

const viaUiVersion = ref(0);
const showExampleModal = ref(false);
const currentExampleUrl = ref('');
const currentExampleTitle = ref('');

const openExampleModal = (url, title) => {
    if (!url) return;
    currentExampleUrl.value = url;
    currentExampleTitle.value = title;
    showExampleModal.value = true;
};

const closeExampleModal = () => {
    showExampleModal.value = false;
    currentExampleUrl.value = '';
    currentExampleTitle.value = '';
};

const isPdfResource = (url) => /\.pdf($|\?)/i.test(url || '');
const isImageResource = (url) => /\.(png|jpe?g|gif|webp|bmp|svg)($|\?)/i.test(url || '');

const currentExampleIsPdf = computed(() => isPdfResource(currentExampleUrl.value));
const currentExampleIsImage = computed(() => isImageResource(currentExampleUrl.value));

// State for display
// Map docId -> { type: 'new'|'reused'|'empty', file: File|null, originalDoc: Object|null, showPreview: boolean }
const documentState = ref({}); 

const filteredCatalogDocuments = computed(() => {
    if (!props.catalog_documents) return [];
    return props.catalog_documents.filter(doc => {
        return doc.via === 'ambas' || doc.via === form.via;
    });
});

const initializeState = ({ reset = false, includePrevious = true } = {}) => {
    if (reset) {
        // Al cambiar de via, se reinicia el estado temporal de cargas para evitar arrastre entre modalidades.
        documentState.value = {};
    }

    if (filteredCatalogDocuments.value) {
        filteredCatalogDocuments.value.forEach(doc => {
            if (!documentState.value[doc.id]) {
                 documentState.value[doc.id] = { type: 'empty', file: null, originalDoc: null, showPreview: false, errorMessage: '' };
            }
        });
    }

    if (includePrevious && props.previous_documents && props.previous_documents.length > 0) {
        props.previous_documents.forEach(prevDoc => {
            const catalogDoc = filteredCatalogDocuments.value.find(d => d.name === prevDoc.name);
            if (catalogDoc) {
                documentState.value[catalogDoc.id] = {
                    type: 'reused',
                    file: null,
                    originalDoc: prevDoc,
                    showPreview: false,
                    errorMessage: ''
                };
            }
        });
    }
};

const handleViaChange = async (newVia) => {
    if (newVia === form.via) return;

    // Fuerza sincronizacion visual de radios controlados por :checked
    // para evitar que el navegador deje marcado temporalmente el radio clicado.
    viaUiVersion.value += 1;

    const hasNewUploadedDocuments = Object.values(documentState.value).some(
        (state) => state && state.type === 'new'
    );

    if (hasNewUploadedDocuments) {
        const confirmed = await alertaConfirmacionEscrita(
            '¿Cambiar vía de solicitud?',
            'Cambiar de vía limpiará los archivos cargados. Esta acción no se puede deshacer.',
            'CAMBIAR'
        );

        if (!confirmed) {
            viaUiVersion.value += 1;
            return;
        }
    }

    form.via = newVia;
    viaUiVersion.value += 1;
    initializeState({ reset: true, includePrevious: true });
};

const requiredDocsCount = computed(() => {
    return filteredCatalogDocuments.value.filter(doc => doc.is_required).length;
});

const readyRequiredDocsCount = computed(() => {
    return filteredCatalogDocuments.value.filter(doc => {
        if (!doc.is_required) return false;
        const state = documentState.value[doc.id];
        return state && state.type !== 'empty';
    }).length;
});

const completionPercentage = computed(() => {
    if (requiredDocsCount.value === 0) return 0;
    return (readyRequiredDocsCount.value / requiredDocsCount.value) * 100;
});

// Initialize immediately
initializeState();

// Also update on mount or when props change to be safe (though setup runs once)
onMounted(() => {
    initializeState();
});

watch(
    () => props.previous_documents,
    () => {
        initializeState();
    },
    { deep: true }
);

const handleFileUpload = (event, docId) => {
    const file = event.target.files[0];
    if (file) {
        processFile(file, docId, event.target);
    }
};

const handleDrop = (event, docId) => {
    const file = event.dataTransfer.files[0];
    if (file) {
        // Validate type (must be PDF)
        if (file.type !== 'application/pdf') {
             if (!documentState.value[docId]) {
                 documentState.value[docId] = { type: 'empty', file: null, originalDoc: null, showPreview: false, errorMessage: '' };
             }
             documentState.value[docId].errorMessage = 'Solo se permiten archivos PDF.';
             return;
        }
        processFile(file, docId, null);
    }
};

const processFile = (file, docId, inputElement) => {
    // Validate size (10MB)
    if (file.size > 10 * 1024 * 1024) {
             // Ensure state exists to set error
             if (!documentState.value[docId]) {
                 documentState.value[docId] = { type: 'empty', file: null, originalDoc: null, showPreview: false, errorMessage: '' };
             }
             documentState.value[docId].errorMessage = 'El archivo pesa más de 10MB.';
             // Reset input if exists
             if (inputElement) inputElement.value = '';
             return;
    }

    documentState.value[docId] = {
        type: 'new',
        file: file,
        originalDoc: null,
        showPreview: false, // Reset preview on new upload
        errorMessage: ''
    };
};

const togglePreview = (docId) => {
    if (documentState.value[docId]) {
        documentState.value[docId].showPreview = !documentState.value[docId].showPreview;
    }
};

const getPreviewUrl = (state) => {
    if (state.type === 'new' && state.file) {
        return URL.createObjectURL(state.file);
    } else if (state.type === 'reused' && state.originalDoc) {
        return route('teacher.documents.stream', state.originalDoc.id);
    }
    return null;
};

const submit = async () => {
    form.announcement_id = props.announcement.id; 
    form.files = [];
    form.file_types = [];
    form.reused_documents = {};

    let hasErrors = false;
    let fileIndexToDocId = {}; // Map form.files index -> docId

    // Reset document errors
    filteredCatalogDocuments.value.forEach(doc => {
        if(documentState.value[doc.id]) {
            documentState.value[doc.id].errorMessage = '';
        }
    });
    
    // Clear form errors
    form.clearErrors();

    filteredCatalogDocuments.value.forEach(doc => {
        const state = documentState.value[doc.id];
        
        // Safety check if state is still missing for some reason
        if (!state) return;

        if (doc.is_required && state.type === 'empty') {
            state.errorMessage = 'Este documento es obligatorio.';
            hasErrors = true;
        }

        if (state.type === 'new') {
            // Track index for error mapping
            const currentIndex = form.files.length;
            fileIndexToDocId[currentIndex] = doc.id;

            form.files.push(state.file);
            form.file_types.push(doc.name);
        } else if (state.type === 'reused') {
            form.reused_documents[doc.name] = state.originalDoc.id;
        }
    });

    if (!form.position_type_id) {
        form.setError('position_type_id', 'El tipo de plaza es obligatorio.');
        hasErrors = true;
    }

    if (hasErrors) {
        return;
    }

    const confirmed = await alertaConfirmacionEscrita(
        '¿Deseas enviar tu solicitud?',
        'Una vez enviada, no podrás modificar los documentos adjuntados.',
        'SOLICITAR'
    );

    if (!confirmed) return;

    alertaCargando('Enviando', 'Por favor espera...');
    form.post(route('teacher.applications.store'), {
        preserveScroll: true,
        onError: (errors) => {
            cerrarAlerta();
            console.error('Validation Errors:', errors);
            
            // Map backend errors to inline errors
            Object.keys(errors).forEach(key => {
                if (key.startsWith('files.')) {
                    const index = parseInt(key.split('.')[1]);
                    const docId = fileIndexToDocId[index];
                    if (docId && documentState.value[docId]) {
                        if (errors[key].includes('greater than')) {
                             documentState.value[docId].errorMessage = 'El archivo pesa más de 10MB.';
                        } else {
                             documentState.value[docId].errorMessage = errors[key];
                        }
                    }
                }
            });
            alertaError('Error', 'Revisa los campos marcados en rojo.');
        },
    });
};
</script>

<template>
    <TeacherLayout>
        <Head title="Solicitar Beca" />
        
        <div class="space-y-6">
            <!-- Header -->
             <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Solicitud de Beca</h1>
                    <div class="flex items-center gap-2 mt-2 text-sm">
                        <svg viewBox="0 0 24 24" class="w-4 h-4 flex-shrink-0" style="fill: #1B396A;">
                            <path :d="mdiBullhorn"/>
                        </svg>
                        <Link :href="route('teacher.announcements.index')" class="text-gray-700 font-medium hover:underline">
                            Convocatorias
                        </Link>
                        <svg xmlns="http://www.w3.org/2000/svg" height="12px" viewBox="0 -960 960 960" width="12px" fill="#9CA3AF">
                            <path d="m321-80-71-71 329-329-329-329 71-71 400 400L321-80Z"/>
                        </svg>
                        <svg viewBox="0 0 24 24" class="w-4 h-4 flex-shrink-0" style="fill: #1B396A;">
                            <path :d="mdiFilePlus"/>
                        </svg>
                        <span class="text-gray-900 font-semibold">Solicitar Beca</span>
                    </div>
                    <p class="text-gray-600 mt-3 text-sm">Convocatoria: <span class="font-semibold text-[var(--institutional-primary)]">{{ announcement.name }}</span></p>
                </div>
                <Link :href="route('teacher.announcements.index')" class="w-full sm:w-auto justify-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition flex items-center gap-2 font-medium">
                    <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="currentColor">
                        <path d="m313-440 224 224-57 56-320-320 320-320 57 56-224 224h487v80H313Z"/>
                    </svg>
                    Regresar
                </Link>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Form (Left) -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- 1. General Information Card -->
                    <div class="bg-white rounded-xl shadow-md border border-gray-200 p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4 border-b pb-2">Información General</h2>
                        
                        <div>
                            <label for="position_type_id" class="block mb-2 text-base text-[var(--institutional-primary)] font-medium text-gray-900">Tipo de Plaza <span class="text-red-500">*</span></label>
                            <VueSelect
                                v-model="form.position_type_id"
                                :options="position_types"
                                :reduce="option => option.id"
                                label="name"
                                placeholder="Buscar o seleccionar un tipo de plaza..."
                                :searchable="true"
                                :clearable="true"
                                :filter-by="(option, label, search) => {
                                    return (option.name || '').toLowerCase().includes(search.toLowerCase()) ||
                                           (option.code || '').toLowerCase().includes(search.toLowerCase())
                                }"
                                class="vue-select-custom"
                                :class="{ 'has-error': form.errors.position_type_id }"
                            >
                                <template #option="option">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-[var(--institutional-primary)]">{{ option.code }}</span>
                                        <span class="text-xs text-gray-500">{{ option.name }}</span>
                                    </div>
                                </template>
                                <template #selected-option="option">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-[var(--institutional-primary)]">{{ option.code }}</span>
                                        <span class="truncate text-gray-700">- {{ option.name }}</span>
                                    </div>
                                </template>
                                <template #no-options="{ search, searching }">
                                    <template v-if="searching">
                                        No se encontraron resultados para <em>{{ search }}</em>.
                                    </template>
                                    <em v-else>Comienza a escribir para buscar...</em>
                                </template>
                            </VueSelect>
                            <div v-if="!form.errors.position_type_id" class="flex items-center gap-1 mt-1 text-xs text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Por favor, selecciona tu tipo de plaza actual</span>
                            </div>
                            <p v-if="form.errors.position_type_id" class="mt-1 text-sm text-red-600">{{ form.errors.position_type_id }}</p>
                        </div>
                    </div>

                    <!-- Selección de Vía -->
                    <div class="bg-white rounded-xl shadow-md border border-gray-200 p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4 border-b pb-2">Vía de Solicitud</h2>
                        <div :key="viaUiVersion" class="space-y-4 flex flex-col md:flex-row md:space-y-0 gap-4">
                            <label class="flex-1 flex items-start gap-3 cursor-pointer p-4 border rounded-lg transition-all" :class="form.via === 'larga' ? 'border-[var(--institutional-primary)] bg-blue-50/50' : 'border-gray-200 hover:bg-gray-50'">
                                <div class="flex items-center h-5">
                                    <input type="radio" name="via" :checked="form.via === 'larga'" value="larga" class="w-4 h-4 text-[var(--institutional-primary)] bg-gray-100 border-gray-300 focus:ring-[var(--institutional-primary)]" @click.prevent="handleViaChange('larga')" />
                                </div>
                                <div class="flex-1">
                                    <span class="block text-sm font-semibold text-gray-900 mb-1">Vía Larga (Evaluación Docente)</span>
                                    <span class="block text-xs text-gray-500">Deberás presentar todos los documentos probatorios requeridos en esta modalidad.</span>
                                </div>
                            </label>
                            <label class="flex-1 flex items-start gap-3 cursor-pointer p-4 border rounded-lg transition-all" :class="form.via === 'corta' ? 'border-[var(--institutional-primary)] bg-blue-50/50' : 'border-gray-200 hover:bg-gray-50'">
                                <div class="flex items-center h-5">
                                    <input type="radio" name="via" :checked="form.via === 'corta'" value="corta" class="w-4 h-4 text-[var(--institutional-primary)] bg-gray-100 border-gray-300 focus:ring-[var(--institutional-primary)]" @click.prevent="handleViaChange('corta')" />
                                </div>
                                <div class="flex-1">
                                    <span class="block text-sm font-semibold text-gray-900 mb-1">Vía Corta (Promoción Docente)</span>
                                    <span class="block text-xs text-gray-500">Aplica si presentas dictamen del Sistema de Promoción Docente. Requiere menos documentos.</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- 2. Documentation Card -->
                    <div class="bg-white rounded-xl shadow-md border border-gray-200 p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4 border-b pb-2">Documentación Requerida</h2>
                        <p class="text-sm text-gray-600 mb-6">
                            Sube los documentos solicitados. Si participaste anteriormente, hemos cargado tus documentos previos automáticamente.
                        </p>

                        <!-- Dynamic Documents List -->
                        <div v-if="filteredCatalogDocuments && filteredCatalogDocuments.length > 0" class="space-y-8">
                            <div v-for="doc in filteredCatalogDocuments" :key="doc.id" class="border-b border-gray-100 pb-8 last:border-0 last:pb-0">
                                 <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-2">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h3 class="text-lg font-bold text-gray-800">{{ doc.name }} <span v-if="doc.is_required" class="text-red-500">*</span></h3>
                                        </div>
                                        <p class="text-sm text-gray-500 mt-1">{{ doc.description || 'Sin descripción' }}</p>
                                    </div>
                                    <!-- Status Badge -->
                                    <div v-if="documentState[doc.id]?.type !== 'empty'" class="flex items-center gap-1 text-green-600 font-medium text-sm">
                                        <svg viewBox="0 0 24 24" class="w-5 h-5" fill="currentColor"><path :d="mdiCheckBold"/></svg>
                                        Listo
                                    </div>
                                </div>

                                <!-- Ver Ejemplo bar — ancho completo -->
                                <div v-if="doc.template_url" class="flex items-center gap-2 w-full mb-4 px-4 py-2.5 rounded-lg bg-white shadow-sm border border-gray-100 border-l-4" style="border-left-color: #1B396A;">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: #1B396A;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span class="text-xs text-gray-600">¿No sabes cómo debe verse este documento?</span>
                                    <button type="button" @click="openExampleModal(doc.template_url, doc.name)" class="text-xs font-bold hover:underline transition ml-1 cursor-pointer" style="color: #1B396A;">
                                        Ver Ejemplo
                                    </button>
                                </div>


                                <!-- FILLED STATE: File Preview / Info -->
                                <div v-if="documentState[doc.id] && documentState[doc.id].type !== 'empty'" class="border-2 border-gray-300 rounded-lg overflow-hidden">
                                    <!-- Header del archivo -->
                                    <div class="bg-gray-100 border-b border-gray-300 p-3 flex items-center justify-between flex-wrap gap-2">
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900 truncate max-w-xs">
                                                {{ documentState[doc.id].type === 'new' ? documentState[doc.id].file.name : (documentState[doc.id].originalDoc.name || doc.name) }}
                                            </p>
                                            <p v-if="documentState[doc.id].type === 'new'" class="text-xs text-gray-600">
                                                Tamaño: {{ (documentState[doc.id].file.size / 1024 / 1024).toFixed(2) }} MB
                                            </p>
                                            <p v-else class="text-xs text-blue-600 font-medium">
                                                Recuperado de solicitud anterior
                                            </p>
                                        </div>
                                        
                                        <!-- Actions -->
                                        <div class="flex items-center gap-2">
                                             <!-- Toggle Preview Button -->
                                             <button
                                                type="button"
                                                @click="togglePreview(doc.id)"
                                                class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 border rounded-lg font-bold transition cursor-pointer text-sm whitespace-nowrap"
                                                :class="documentState[doc.id].showPreview ? 'bg-[var(--institutional-primary)] text-white border-[var(--institutional-primary)]' : 'text-[var(--institutional-primary)] border-[var(--institutional-primary)] hover:bg-[var(--institutional-primary)] hover:text-white'"
                                                :title="documentState[doc.id].showPreview ? 'Ocultar Vista Previa' : 'Ver Vista Previa'"
                                             >
                                                <svg xmlns="http://www.w3.org/2000/svg" height="18px" viewBox="0 -960 960 960" width="18px" fill="currentColor">
                                                    <path v-if="!documentState[doc.id].showPreview" d="M480-320q75 0 127.5-52.5T660-500q0-75-52.5-127.5T480-680q-75 0-127.5 52.5T300-500q0 75 52.5 127.5T480-320Zm0-72q-45 0-76.5-31.5T372-500q0-45 31.5-76.5T480-608q45 0 76.5 31.5T588-500q0 45-31.5 76.5T480-392Zm0 192q-146 0-266-81.5T40-500q54-137 174-218.5T480-800q146 0 266 81.5T920-500q-54 137-174 218.5T480-200Z"/>
                                                    <path v-else d="m644-428-58-58q9-47-27-88t-93-32l-58-58q17-8 34.5-12t37.5-4q75 0 127.5 52.5T660-500q0 20-4 37.5T644-428Zm128 126-58-56q38-29 67.5-63.5T832-500q-50-101-143.5-160.5T480-720q-29 0-57 4t-55 12l-62-62q41-17 84-25.5t90-8.5q151 0 269 83.5T920-500q-23 59-60.5 109.5T772-302Zm20 246L624-222q-35 11-70.5 16.5T480-200q-151 0-269-83.5T40-500q21-53 53-98.5t73-81.5L56-792l56-56 736 736-56 56ZM222-624q-29 26-53 57t-41 67q50 101 143.5 160.5T480-280q20 0 39-2.5t39-5.5l-36-38q-11 3-21 4.5t-21 1.5q-75 0-127.5-52.5T300-500q0-11 1.5-21t4.5-21l-84-82Zm319 93Zm-151 75Z"/>
                                                </svg>
                                                {{ documentState[doc.id].showPreview ? 'Ocultar' : 'Visualizar' }}
                                             </button>

                                             <label :for="'doc-upload-' + doc.id" class="cursor-pointer px-4 py-2 bg-[var(--institutional-primary)] text-white text-sm rounded hover:bg-[#0f2347] transition shadow-sm font-medium">
                                                Cambiar Archivo
                                                <input :id="'doc-upload-' + doc.id" type="file" class="hidden" @change="(e) => handleFileUpload(e, doc.id)" accept=".pdf" />
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <!-- Visor integrado (Collapsible) -->
                                    <div v-if="documentState[doc.id].showPreview" class="bg-gray-100 h-[500px] border-t border-gray-200">
                                        <!-- PDF Preview -->
                                        <iframe 
                                            v-if="getPreviewUrl(documentState[doc.id])" 
                                            :src="getPreviewUrl(documentState[doc.id])" 
                                            class="w-full h-full border-0"
                                            title="Visor de archivo"
                                        ></iframe>
                                        
                                        <!-- Fallback -->
                                        <div v-else class="h-full flex items-center justify-center bg-gray-50 p-6">
                                            <div class="text-center">
                                                <svg class="w-16 h-16 mx-auto text-[var(--institutional-primary)] mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                                <p class="text-gray-900 font-bold mb-2 text-lg">Documento Cargado</p>
                                                <p class="text-gray-600 font-medium mb-2">Vista previa no disponible</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- EMPTY STATE: Drop Zone -->
                                <div v-else class="flex items-center justify-center w-full">
                                    <label 
                                        :for="'doc-upload-' + doc.id" 
                                        class="flex flex-col items-center justify-center w-full h-48 bg-gradient-to-br from-[#F3F4F6] to-[#E5E7EB] border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:bg-gradient-to-br hover:from-[#EFF6FF] hover:to-[#DBEAFE] hover:border-[var(--institutional-primary)] transition-all relative"
                                        @dragover.prevent
                                        @drop.prevent="(e) => handleDrop(e, doc.id)"
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
                                        <input :id="'doc-upload-' + doc.id" type="file" class="hidden" @change="(e) => handleFileUpload(e, doc.id)" accept=".pdf" />
                                    </label>
                                </div>

                                <!-- Error Message -->
                                <p v-if="documentState[doc.id]?.errorMessage" class="mt-2 text-sm text-red-600 font-medium flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                    {{ documentState[doc.id].errorMessage }}
                                </p>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div v-else class="flex flex-col items-center justify-center py-12 text-gray-500">
                             <p>No hay documentos configurados.</p>
                        </div>
                    </div>
                </div>

                <!-- Summary / Actions (Right Checkbox) -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-xl shadow-md border border-gray-200 p-6 sticky top-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Resumen</h3>
                        
                        <!-- Progress -->
                        <div class="space-y-3 mb-6">
                             <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-600">Documentos listos:</span>
                                <span class="font-bold text-gray-900">
                                    {{ readyRequiredDocsCount }} / {{ requiredDocsCount }} (Req)
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                <div class="bg-[var(--institutional-primary)] h-2.5 rounded-full transition-all duration-500" :style="{ width: `${completionPercentage}%` }"></div>
                            </div>
                        </div>

                        <div class="relative px-5 py-4 rounded-lg bg-white shadow-sm border border-gray-100 border-l-4 mb-6" style="border-left-color: #1B396A;">
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0 mt-1" style="color: #1B396A;">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <span class="text-[10px] uppercase font-bold tracking-widest mb-1 block opacity-60" style="color: #1B396A;">Nota importante</span>
                                    <p class="text-xs text-gray-700 leading-relaxed font-medium">
                                        Al enviar esta solicitud, confirmas que la información proporcionada es verídica y aceptas los términos y condiciones de la convocatoria.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <button @click="submit" :disabled="form.processing"
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-[var(--institutional-primary)] hover:bg-[#0f2347] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--institutional-primary)] disabled:opacity-50 disabled:cursor-not-allowed transition cursor-pointer">
                            <span v-if="form.processing">Enviando...</span>
                            <span v-else>Enviar Solicitud</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <Teleport to="body">
            <Transition name="modal">
                <div v-if="showExampleModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>

                    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-6xl h-[85vh] sm:h-[90vh] flex flex-col">
                        <div class="flex items-center justify-between p-6 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-900">{{ currentExampleTitle }}</h2>
                            <button @click="closeExampleModal" class="text-gray-400 hover:text-gray-800 transition-colors cursor-pointer">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <div class="flex-1 overflow-hidden bg-gray-100">
                            <iframe
                                v-if="currentExampleIsPdf"
                                :src="currentExampleUrl"
                                class="w-full h-full"
                                frameborder="0"
                            ></iframe>

                            <div
                                v-else-if="currentExampleIsImage"
                                class="w-full h-full overflow-auto flex items-center justify-center p-4"
                            >
                                <img
                                    :src="currentExampleUrl"
                                    :alt="currentExampleTitle"
                                    class="max-w-full max-h-full object-contain mx-auto"
                                />
                            </div>

                            <div v-else class="w-full h-full flex items-center justify-center p-8">
                                <a
                                    :href="currentExampleUrl"
                                    target="_blank"
                                    class="px-4 py-2 rounded-md bg-[var(--institutional-primary)] text-white font-medium hover:bg-[var(--institutional-primary-dark)]"
                                >
                                    Abrir documento
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </TeacherLayout>
</template>
<style scoped>
.vue-select-custom :deep(.vs__dropdown-toggle) {
    background: #F3F4F6;
    border: none;
    border-bottom: 2px solid #D1D5DB;
    border-radius: 0.5rem;
    padding: 2px 0;
    transition: all 0.2s;
}

.vue-select-custom.has-error :deep(.vs__dropdown-toggle) {
    border-bottom-color: #EF4444;
}

.vue-select-custom :deep(.vs__dropdown-toggle):hover {
    border-bottom-color: #1B396A;
}

.vue-select-custom :deep(.vs--open .vs__dropdown-toggle) {
    border-bottom-color: #1B396A;
}

.vue-select-custom :deep(.vs__search) {
    margin: 0;
    padding: 0.625rem 0.75rem;
    font-size: 0.875rem;
    color: #111827;
}

.vue-select-custom :deep(.vs__selected) {
    position: relative;
    z-index: 10;
    margin: 0;
    padding: 0 0.75rem;
    color: #111827;
    font-size: 0.875rem;
}

.vue-select-custom :deep(.vs__dropdown-menu) {
    border: 1px solid #E5E7EB;
    border-radius: 0.5rem;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    margin-top: 4px;
}

.vue-select-custom :deep(.vs__dropdown-option) {
    padding: 0.625rem 0.75rem;
    font-size: 0.875rem;
}

.vue-select-custom :deep(.vs__dropdown-option--highlight) {
    background: #1B396A;
    color: white !important;
}

.vue-select-custom :deep(.vs__dropdown-option--highlight span) {
    color: white !important;
}

.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}

.modal-enter-active .relative,
.modal-leave-active .relative {
    transition: transform 0.3s ease;
}

.modal-enter-from .relative,
.modal-leave-to .relative {
    transform: scale(0.95);
}
</style>


