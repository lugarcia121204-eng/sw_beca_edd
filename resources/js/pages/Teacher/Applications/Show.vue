<script setup>
import { Head, Link } from '@inertiajs/vue3';
import TeacherLayout from '@/layouts/TeacherLayout.vue';
import { 
    mdiFileDocumentMultiple, 
    mdiEye, 
    mdiEyeOff,
    mdiDownload, 
    mdiCheckCircle, 
    mdiCloseCircle, 
    mdiClockOutline,
    mdiChevronRight
} from '@mdi/js';
import { ref } from 'vue';

const props = defineProps({
    application: Object,
});

// State to track preview visibility for each document
const documentsState = ref({});

// State for the acceptance letter inline preview
const showAcceptancePreview = ref(false);

const acceptanceUrl = route('teacher.documents.downloadAcceptance', props.application.id);

const togglePreview = (docId) => {
    if (!documentsState.value[docId]) {
        documentsState.value[docId] = { showPreview: false };
    }
    documentsState.value[docId].showPreview = !documentsState.value[docId].showPreview;
};

const getPreviewUrl = (doc) => {
    return route('teacher.documents.stream', doc.id);
};

const getFileIcon = (type) => {
    return 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5-3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z'; 
};
</script>

<template>
    <TeacherLayout>
        <Head title="Detalles de Solicitud" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="w-full sm:w-auto">
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Detalles de la Solicitud</h1>
                    <div class="flex items-center gap-2 mt-2 text-sm">
                        <svg viewBox="0 0 24 24" class="w-4 h-4 flex-shrink-0" style="fill: #1B396A;">
                            <path :d="mdiFileDocumentMultiple"/>
                        </svg>
                        <Link :href="route('teacher.dashboard')" class="text-gray-700 font-medium hover:text-[var(--institutional-primary)]">Inicio</Link>
                        <svg viewBox="0 0 24 24" class="w-4 h-4 text-gray-400" fill="currentColor">
                            <path :d="mdiChevronRight"/>
                        </svg>
                        <span class="text-gray-900 font-semibold">Detalles de Solicitud</span>
                    </div>
                </div>
                <Link :href="route('teacher.dashboard')" class="w-full sm:w-auto justify-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition flex items-center gap-2 font-medium">
                    <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="currentColor">
                        <path d="m313-440 224 224-57 56-320-320 320-320 57 56-224 224h487v80H313Z"/>
                    </svg>
                    Regresar
                </Link>
            </div>
            
            <!-- Status Banner (Premium Minimalist Sync) -->
            <transition enter-active-class="transition duration-500 ease-out" enter-from-class="transform -translate-y-4 opacity-0" enter-to-class="transform translate-y-0 opacity-100">
                <div 
                    class="relative flex items-center gap-4 px-5 py-4 rounded-lg bg-white shadow-sm border border-gray-100"
                    :style="{ 
                        borderLeft: `5px solid ${
                            application.status === 'approved' ? '#10A558' : 
                            application.status === 'rejected' ? '#6B7280' : '#C9A800'
                        }`
                    }"
                >
                    <div class="flex-shrink-0" :style="{ 
                        color: application.status === 'approved' ? '#10A558' : 
                               application.status === 'rejected' ? '#6B7280' : '#C9A800'
                    }">
                        <svg v-if="application.status === 'approved'" viewBox="0 0 24 24" class="w-6 h-6" style="fill: currentColor"><path :d="mdiCheckCircle"/></svg>
                        <svg v-else-if="application.status === 'rejected'" viewBox="0 0 24 24" class="w-6 h-6" style="fill: currentColor"><path :d="mdiCloseCircle"/></svg>
                        <svg v-else viewBox="0 0 24 24" class="w-6 h-6" style="fill: currentColor"><path :d="mdiClockOutline"/></svg>
                    </div>

                    <div class="flex flex-col">
                        <span class="text-[10px] uppercase font-bold tracking-widest opacity-60 mb-0.5" :style="{ 
                            color: application.status === 'approved' ? '#10A558' : 
                                   application.status === 'rejected' ? '#6B7280' : '#C9A800'
                        }">
                            Estado de Solicitud
                        </span>
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-bold leading-tight text-gray-800">
                                {{ 
                                    application.status === 'approved' ? 'Solicitud Aprobada' : 
                                    application.status === 'rejected' ? 'Solicitud No Aprobada' : 'Solicitud Pendiente de Revisión' 
                                }}
                            </span>
                            <p class="text-[13px] text-gray-600 leading-snug" v-if="application.status === 'pending'">
                                Tu solicitud está siendo revisada por el comité. Te notificaremos cuando haya una actualización.
                            </p>
                            <p class="text-[13px] text-gray-600 leading-snug" v-else-if="application.status === 'approved'">
                                Tras la revisión de su expediente, su solicitud ha sido dictaminada exitosamente como <strong class="text-green-700">APROBADA</strong>.
                            </p>
                            <div v-else-if="application.status === 'rejected' && application.admin_comment" class="mt-1">
                                <p class="text-[13px] text-gray-700 italic">"{{ application.admin_comment }}"</p>
                            </div>
                        </div>
                    </div>

                    <!-- Action buttons for approved state -->
                    <div v-if="application.status === 'approved'" class="ml-auto flex items-center gap-2">
                        <button 
                            @click="showAcceptancePreview = !showAcceptancePreview"
                            class="flex items-center gap-2 px-3 py-2 border rounded-lg font-bold transition cursor-pointer text-xs uppercase shadow-sm"
                            :class="showAcceptancePreview ? 'bg-green-600 text-white border-green-600' : 'bg-green-50 text-green-700 border-green-200 hover:bg-green-600 hover:text-white'"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path v-if="!showAcceptancePreview" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path v-if="!showAcceptancePreview" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                            {{ showAcceptancePreview ? 'Cerrar' : 'Ver Carta' }}
                        </button>
                    </div>
                </div>
            </transition>

            <!-- Inline Acceptance Letter Preview -->
            <div v-if="application.status === 'approved' && showAcceptancePreview" class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden animate-fadeIn">
                <div class="flex justify-between items-center px-6 py-4 border-b border-gray-200">
                    <h3 class="font-bold text-gray-800 text-lg flex items-center gap-2">
                        <svg viewBox="0 0 24 24" class="w-5 h-5 flex-shrink-0" style="fill: #1B396A;">
                            <path :d="mdiFileDocumentMultiple"/>
                        </svg>
                        Carta de Aceptación
                    </h3>
                    <div class="flex items-center gap-2">
                        <a 
                            :href="acceptanceUrl" 
                            download
                            class="flex items-center gap-2 px-3 py-2 bg-[var(--institutional-primary)] text-white rounded-lg font-bold text-xs uppercase shadow-sm hover:bg-[#152b52] transition"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Descargar
                        </a>
                        <button @click="showAcceptancePreview = false" class="text-gray-400 hover:text-gray-600 p-1.5 hover:bg-gray-100 rounded-lg transition cursor-pointer" title="Cerrar">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="w-full h-[700px] bg-gray-50 relative">
                    <div class="absolute inset-0 flex items-center justify-center text-gray-400 z-0 text-center">
                        <div class="flex flex-col items-center gap-2">
                            <svg class="w-10 h-10 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <span>Cargando carta...</span>
                        </div>
                    </div>
                    <iframe :src="acceptanceUrl" class="w-full h-full relative z-10" frameborder="0"></iframe>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-md rounded-lg border border-gray-200 p-8">
                <!-- Info Header -->
                <div class="flex justify-between items-start mb-8 relative">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">Información de la Solicitud</h3>
                    </div>
                </div>

                <!-- Info Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-y-8 gap-x-12 mb-10">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Profesor</p>
                        <p class="text-gray-900 font-medium text-base">{{ application.teacher?.name || application.user?.name || 'Usuario' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Área Prioritaria</p>
                        <p class="text-gray-900 font-medium text-base">{{ application.teacher?.department || application.user?.priority_area?.name || 'No asignado' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Institución</p>
                        <p class="text-gray-900 text-base leading-snug">{{ application.campus || application.user?.institution?.name || 'No registrado' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Tipo de Beca</p>
                        <p class="text-gray-900 text-base">{{ application.announcement?.name }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Tipo de Plaza</p>
                        <p class="text-gray-900 text-base">{{ application.position_full_type || 'No especificado' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-2">Vía de Solicitud</p>
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
                    <div>
                         <p class="text-sm font-medium text-gray-600 mb-1">Fecha de Solicitud</p>
                         <p class="text-gray-900 text-base">{{ new Date(application.created_at).toLocaleDateString() }}</p>
                    </div>
                </div>

                <!-- Documentation -->
                <div class="border-t border-gray-200 pt-8">
                    <h3 class="text-lg font-bold text-gray-900 mb-6">Documentación</h3>
                    
                    <div class="grid grid-cols-1 gap-4">
                        <div v-if="!application.documents || application.documents.length === 0" class="text-gray-500 italic">
                            No hay documentos disponibles.
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
                                
                                <!-- Actions (Visualizar Only Sync) -->
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

                            <!-- Inline Preview (Premium Layout Sync) -->
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
                                    <div class="w-full h-[55vh] sm:h-[65vh] lg:h-[600px] bg-white rounded-xl overflow-hidden border border-gray-300 shadow-inner relative">
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
    </TeacherLayout>
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

