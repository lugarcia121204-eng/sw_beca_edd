<script setup>
import { Head, Link } from '@inertiajs/vue3';
import LayoutAuthenticated from '@/layouts/LayoutAuthenticated.vue';
import { mdiFileDocumentMultiple, mdiBookOpenPageVariant, mdiEye, mdiEyeOff, mdiDownload } from '@mdi/js';
import { ref } from 'vue';

const props = defineProps({
    application: {
        type: Object,
        required: true,
    },
    title: {
        type: String,
        required: true,
    },
    routeName: {
        type: String,
        required: true,
    },
});

const documentsState = ref({});

const togglePreview = (docId) => {
    if (!documentsState.value[docId]) {
        documentsState.value[docId] = { showPreview: false };
    }
    documentsState.value[docId].showPreview = !documentsState.value[docId].showPreview;
};

const getPreviewUrl = (doc) => {
    return route('catalog.documents.streamDocente', doc.id);
};

const getFileIcon = (type) => {
    return 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5-3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z'; 
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
                        <Link :href="route(`${routeName}index`, { tab: 'docentes' })" class="flex items-center gap-2 hover:text-[var(--institutional-primary)] transition">
                            <svg viewBox="0 0 24 24" class="w-4 h-4 flex-shrink-0" style="fill: #1B396A;">
                                <path :d="mdiFileDocumentMultiple"/>
                            </svg>
                            <span class="text-gray-700 font-medium">Documentos</span>
                        </Link>
                        <svg xmlns="http://www.w3.org/2000/svg" height="12px" viewBox="0 -960 960 960" width="12px" fill="#9CA3AF">
                            <path d="m321-80-71-71 329-329-329-329 71-71 400 400L321-80Z"/>
                        </svg>
                        <span class="text-gray-900 font-semibold">Detalles</span>
                    </div>
                </div>
                <Link :href="route(`${routeName}index`, { tab: 'docentes' })" class="w-full sm:w-auto justify-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition flex items-center gap-2 font-medium">
                    <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="currentColor">
                        <path d="m313-440 224 224-57 56-320-320 320-320 57 56-224 224h487v80H313Z"/>
                    </svg>
                    Regresar
                </Link>
            </div>

            <!-- Información del Profesor -->
            <div class="bg-white overflow-hidden shadow-md rounded-lg border border-gray-200 p-8">
                <div class="flex justify-between items-start mb-8 relative">
                    <h3 class="text-xl font-bold text-gray-900">Información del Profesor</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-y-8 gap-x-12 mb-10">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Profesor</p>
                        <p class="text-gray-900 font-medium text-base">{{ application.teacher?.name || 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Correo Electrónico</p>
                        <p class="text-gray-900 font-medium text-base">{{ application.teacher?.email || 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Área</p>
                        <p class="text-gray-900 font-medium text-base leading-snug">{{ application.teacher?.department || 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Subárea</p>
                        <p class="text-gray-900 font-medium text-base">{{ application.teacher?.sub_area || 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Institución</p>
                        <p class="text-gray-900 font-medium text-base leading-snug">{{ application.campus || 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Estado</p>
                        <p class="text-gray-900 font-medium text-base">{{ application.teacher?.state || 'N/A' }}</p>
                    </div>
                    <div v-if="application.announcement" class="md:col-span-2">
                        <p class="text-sm font-medium text-gray-600 mb-1">Convocatoria</p>
                        <p class="text-gray-900 font-medium text-base leading-snug">{{ application.announcement.name }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-2">Vía Seleccionada</p>
                        <div class="inline-flex">
                            <span v-if="application.via === 'larga'" class="inline-flex items-center px-4 py-2.5 rounded-lg text-xs font-bold uppercase tracking-wider text-white shadow-md hover:shadow-lg transition" style="background-color: #1B396A; border: 1px solid #0f2347;">
                                Vía Larga
                            </span>
                            <span v-else-if="application.via === 'corta'" class="inline-flex items-center px-4 py-2.5 rounded-lg text-xs font-bold uppercase tracking-wider text-white shadow-md hover:shadow-lg transition" style="background-color: #10A558; border: 1px solid #0d8d47;">
                                Vía Corta
                            </span>
                            <span v-else class="inline-flex items-center px-4 py-2.5 rounded-lg text-xs font-bold uppercase tracking-wider bg-gray-100 text-gray-600 border border-gray-300">
                                No Especificada
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Documentación -->
                <div class="border-t border-gray-200 pt-8">
                    <h3 class="text-lg font-bold text-gray-900 mb-6">Documentación</h3>
                    
                    <div class="grid grid-cols-1 gap-4">
                        <div v-if="!application.documents || application.documents.length === 0" 
                            class="text-gray-500 italic py-8 bg-gray-50 rounded-lg text-center font-medium">
                            No hay documentos cargados.
                        </div>

                        <div v-for="doc in application.documents" :key="doc.id" 
                            class="border border-gray-200 rounded-xl p-5 hover:bg-gray-50 transition shadow-sm"
                            :class="{ 'bg-blue-50/50 ring-1 ring-[var(--institutional-primary)]/10': documentsState[doc.id]?.showPreview }">
                            
                            <div class="flex flex-col sm:flex-row items-center justify-between gap-6">
                                <div class="flex items-center gap-4 w-full sm:w-auto min-w-0">
                                    <div class="flex-shrink-0 w-12 h-12 flex items-center justify-center bg-gray-100 rounded-xl text-[var(--institutional-primary)]"
                                         :class="{ 'bg-blue-100': documentsState[doc.id]?.showPreview }">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" :d="getFileIcon(doc.file_type)" />
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <span class="font-bold text-gray-900 block text-base md:text-lg truncate" :title="doc.name">{{ doc.name }}</span>
                                        <p class="text-xs text-gray-500 font-medium uppercase tracking-wider mt-0.5">{{ doc.file_type === 'pdf' ? 'Documento PDF' : 'Archivo' }}</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-center gap-2 w-full sm:w-auto justify-end flex-shrink-0">
                                    <button 
                                        v-if="doc.file_type === 'pdf'" 
                                        @click="togglePreview(doc.id)" 
                                        class="inline-flex items-center justify-center gap-2 px-4 py-2 border rounded-lg font-bold transition cursor-pointer text-xs uppercase whitespace-nowrap shadow-sm"
                                        :class="documentsState[doc.id]?.showPreview 
                                            ? 'bg-[var(--institutional-primary)] text-white border-[var(--institutional-primary)]' 
                                            : 'text-[var(--institutional-primary)] border-[var(--institutional-primary)] hover:bg-[var(--institutional-primary)] hover:text-white'"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" height="18px" viewBox="0 -960 960 960" width="18px" fill="currentColor">
                                            <path v-if="!documentsState[doc.id]?.showPreview" d="M480-320q75 0 127.5-52.5T660-500q0-75-52.5-127.5T480-680q-75 0-127.5 52.5T300-500q0 75 52.5 127.5T480-320Zm0-72q-45 0-76.5-31.5T372-500q0-45 31.5-76.5T480-608q45 0 76.5 31.5T588-500q0 45-31.5 76.5T480-392Zm0 192q-146 0-266-81.5T40-500q54-137 174-218.5T480-800q146 0 266 81.5T920-500q-54 137-174 218.5T480-200Z"/>
                                            <path v-else d="m644-428-58-58q9-47-27-88t-93-32l-58-58q17-8 34.5-12t37.5-4q75 0 127.5 52.5T660-500q0 20-4 37.5T644-428Zm128 126-58-56q38-29 67.5-63.5T832-500q-50-101-143.5-160.5T480-720q-29 0-57 4t-55 12l-62-62q41-17 84-25.5t90-8.5q151 0 269 83.5T920-500q-23 59-60.5 109.5T772-302Zm20 246L624-222q-35 11-70.5 16.5T480-200q-151 0-269-83.5T40-500q21-53 53-98.5t73-81.5L56-792l56-56 736 736-56 56ZM222-624q-29 26-53 57t-41 67q50 101 143.5 160.5T480-280q20 0 39-2.5t39-5.5l-36-38q-11 3-21 4.5t-21 1.5q-75 0-127.5-52.5T300-500q0-11 1.5-21t4.5-21l-84-82Zm319 93Zm-151 75Z"/>
                                        </svg>
                                        {{ documentsState[doc.id]?.showPreview ? 'Ocultar' : 'Visualizar' }}
                                    </button>

                                </div>
                            </div>

                            <!-- Inline Preview -->
                            <div v-if="documentsState[doc.id]?.showPreview" class="mt-6 pt-6 border-t border-gray-200 w-full animate-fadeIn">
                                <div class="flex flex-col gap-4">
                                    <div class="flex justify-between items-center">
                                        <h3 class="font-bold text-gray-800 text-lg flex items-center gap-2">
                                            <svg viewBox="0 0 24 24" class="w-5 h-5 flex-shrink-0" style="fill: #1B396A;">
                                                <path :d="mdiFileDocumentMultiple"/>
                                            </svg>
                                            Vista Previa: {{ doc.name }}
                                        </h3>
                                        <button @click="togglePreview(doc.id)" class="text-gray-400 hover:text-gray-600 p-1.5 hover:bg-gray-100 rounded-lg transition cursor-pointer" title="Cerrar vista previa">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="w-full h-[55vh] sm:h-[65vh] lg:h-[600px] border border-gray-300 rounded-xl overflow-hidden bg-white shadow-inner relative">
                                        <div class="absolute inset-0 flex items-center justify-center text-gray-400 z-0 text-center">
                                            <div class="flex flex-col items-center gap-2">
                                                <svg class="w-10 h-10 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                                <span>Cargando vista previa...</span>
                                            </div>
                                        </div>
                                        <iframe :src="getPreviewUrl(doc)" class="w-full h-full relative z-10" frameborder="0"></iframe>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </LayoutAuthenticated>
</template>

<style scoped>
.animate-fadeIn {
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

