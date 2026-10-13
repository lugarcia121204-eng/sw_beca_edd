<script setup>
import { Head, Link } from '@inertiajs/vue3';
import EvaluatorLayout from '@/layouts/EvaluatorLayout.vue';
import { ref, computed } from 'vue';
import { 
    mdiHome, 
    mdiFileDocumentMultiple, 
    mdiCheckCircle, 
    mdiCloseCircle,
    mdiMessageText,
    mdiChevronRight,
    mdiShieldCheck
} from '@mdi/js';

const props = defineProps({
    evaluation: Object,
    application: Object,
    rubric: Object,
    teacher: Object,
});

// Logic for Document Preview (Standardized)
const documentsState = ref({});

const togglePreview = (docId) => {
    if (!documentsState.value[docId]) {
        documentsState.value[docId] = { showPreview: false };
    }
    documentsState.value[docId].showPreview = !documentsState.value[docId].showPreview;
};

const getPreviewUrl = (doc) => {
    return route('evaluator.documents.stream', doc.id);
};

const getFileIcon = (type) => {
    return 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5-3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z'; 
};

// Normalize documents list
const documentsList = computed(() => {
    const docs = props.application.documents;
    if (!docs) return [];
    return Array.isArray(docs) ? docs : (docs.data || []);
});

// Respuestas guardadas en la evaluación
const respuestas = computed(() => props.evaluation.answers || {});

const getSelectedOption = (question) => {
    const respuesta = respuestas.value[question.id];
    if (!respuesta) return null;
    return question.options.find(opt => opt.id === respuesta.option_id);
};

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('es-ES', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
};

const isEvaluatedByAdmin = computed(() => {
    return props.evaluation.status === 'pending' && props.application.status !== 'pending';
});
</script>

<template>
    <EvaluatorLayout>
        <Head title="Detalles de Evaluación" />

        <div class="space-y-6">
            <!-- Header with Breadcrumbs -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="w-full sm:w-auto">
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Detalles de Evaluación</h1>
                    <div class="flex flex-wrap items-center gap-2 mt-2 text-sm">
                        <svg viewBox="0 0 24 24" class="w-4 h-4 flex-shrink-0" style="fill: #1B396A;">
                            <path :d="mdiFileDocumentMultiple"/>
                        </svg>
                        <Link :href="route('evaluator.evaluations.index')" class="text-gray-700 font-medium hover:text-[var(--institutional-primary)]">Evaluaciones</Link>
                         <svg viewBox="0 0 24 24" class="w-4 h-4 text-gray-400" style="fill: currentColor">
                            <path :d="mdiChevronRight"/>
                        </svg>
                        <span class="text-gray-900 font-semibold">Detalles</span>
                    </div>
                </div>
                <Link :href="route('evaluator.evaluations.index')" class="w-full sm:w-auto justify-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition flex items-center gap-2 font-medium">
                    <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="currentColor">
                        <path d="m313-440 224 224-57 56-320-320 320-320 57 56-224 224h487v80H313Z"/>
                    </svg>
                    Regresar
                </Link>
            </div>

            <!-- Status Banner (Premium) -->
            <transition enter-active-class="transition duration-500 ease-out" enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0">
                <div 
                    class="relative flex items-start sm:items-center gap-3 sm:gap-4 px-4 sm:px-5 py-4 rounded-lg bg-white shadow-sm border border-gray-100"
                    :style="{ borderLeft: `5px solid ${ isEvaluatedByAdmin ? '#7C3AED' : evaluation.status === 'approved' ? '#10A558' : '#EF4444' }` }"
                >
                    <div class="p-2 flex-shrink-0">
                        <svg viewBox="0 0 24 24" class="w-7 h-7"
                            :style="{ fill: isEvaluatedByAdmin ? '#7C3AED' : evaluation.status === 'approved' ? '#10A558' : '#EF4444' }"
                        >
                            <path v-if="!isEvaluatedByAdmin && evaluation.status === 'approved'" :d="mdiCheckCircle"/>
                            <path v-else-if="!isEvaluatedByAdmin && evaluation.status === 'rejected'" :d="mdiCloseCircle"/>
                            <path v-else :d="mdiShieldCheck"/>
                        </svg>
                    </div>
                    <div>
                        <p 
                            class="text-xs font-bold uppercase tracking-wider mb-0.5"
                            :style="{ color: isEvaluatedByAdmin ? '#7C3AED' : evaluation.status === 'approved' ? '#10A558' : '#EF4444' }"
                        >
                            <span v-if="isEvaluatedByAdmin">Gestión Administrativa</span>
                            <span v-else-if="evaluation.status === 'approved'">Estado de Evaluación</span>
                            <span v-else>Estado de Evaluación</span>
                        </p>
                        <h3 class="font-bold text-gray-900 text-sm sm:text-base">
                            <span v-if="isEvaluatedByAdmin">Resuelta por Administración</span>
                            <span v-else-if="evaluation.status === 'approved'">Evaluación Completada — Aprobada</span>
                            <span v-else>Evaluación Completada — Rechazada</span>
                        </h3>
                        <p class="text-xs sm:text-sm text-gray-500 mt-0.5">
                            <span v-if="isEvaluatedByAdmin">Esta solicitud fue tomada y resuelta directamente por un administrador. No requieres realizar ninguna acción en este expediente.</span>
                            <span v-else>Evaluada el {{ evaluation.updated_at ? formatDate(evaluation.updated_at) : 'Fecha no disponible' }}</span>
                        </p>
                    </div>
                </div>
            </transition>

            <!-- Comentario de Rechazo (si existe y NO fue por admin) -->
            <transition enter-active-class="transition duration-500 ease-out" enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0">
                <div v-if="!isEvaluatedByAdmin && evaluation.status === 'rejected' && evaluation.comment"
                    class="relative flex items-start gap-3 sm:gap-4 px-4 sm:px-5 py-4 rounded-lg bg-white shadow-sm border border-gray-100"
                    style="border-left: 5px solid #EF4444;"
                >
                    <div class="p-2 flex-shrink-0">
                        <svg viewBox="0 0 24 24" class="w-7 h-7" style="fill: #EF4444;"><path :d="mdiMessageText"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider mb-0.5" style="color: #EF4444;">Motivo de Rechazo</p>
                        <h3 class="font-bold text-gray-900 text-sm sm:text-base">Comentario del Evaluador</h3>
                        <p class="text-xs sm:text-sm text-gray-900 mt-0.5 italic break-words">"{{ evaluation.comment }}"</p>
                    </div>
                </div>
            </transition>

            <div class="space-y-6">
                
                <!-- Información General (Applicant) -->
                <div class="bg-white p-4 sm:p-6 rounded-xl shadow-sm border border-gray-100 relative">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-gray-100 pb-4 mb-6 gap-2 sm:gap-4">
                        <h2 class="text-lg font-bold text-gray-900">Información General</h2>
                     </div>
                     
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                        <div>
                            <h3 class="text-xs uppercase text-gray-500 font-semibold mb-1">Docente</h3>
                            <p class="text-md font-medium text-gray-900">{{ teacher.name || 'Completar datos' }}</p>
                        </div>
                        
                        <div>
                            <h3 class="text-xs uppercase text-gray-500 font-semibold mb-1">Institución</h3>
                            <p class="text-md font-medium text-gray-900">{{ teacher.institution?.name || 'No registrada' }}</p>
                        </div>
                        
                        <div>
                            <h3 class="text-xs uppercase text-gray-500 font-semibold mb-1">Convocatoria</h3>
                            <p class="text-md font-medium text-gray-900">{{ application.announcement?.name || 'General' }}</p>
                        </div>

                        <div>
                            <h3 class="text-xs uppercase text-gray-500 font-semibold mb-1">Área de Procedencia</h3>
                            <p class="text-md font-medium text-gray-900">{{ teacher.priority_area?.name || 'No registrada' }}</p>
                        </div>

                        <div>
                            <h3 class="text-xs uppercase text-gray-500 font-semibold mb-1">Subárea</h3>
                            <p class="text-md font-medium text-gray-900">{{ teacher.sub_area?.name || 'No registrada' }}</p>
                        </div>

                         <div>
                            <h3 class="text-xs uppercase text-gray-500 font-semibold mb-1">Fecha de Solicitud</h3>
                            <p class="text-md font-medium text-gray-900">{{ formatDate(application.created_at) }}</p>
                        </div>

                        <div>
                            <h3 class="text-xs uppercase text-gray-500 font-semibold mb-1">Tipo de Plaza</h3>
                            <p class="text-md font-medium text-gray-900">{{ application.position_full_type || 'No especificado' }}</p>
                        </div>
                        <div>
                            <h3 class="text-xs uppercase text-gray-500 font-semibold mb-2">Vía de Solicitud</h3>
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
                </div>

                <!-- Documentación -->
                <div class="bg-white p-4 sm:p-6 rounded-xl shadow-sm border border-gray-100">
                    <h2 class="text-lg font-bold text-gray-900 mb-6 border-b border-gray-100 pb-2">Documentación</h2>

                    <div class="grid grid-cols-1 gap-4">
                        <div v-if="documentsList.length === 0" class="text-gray-500 italic text-center py-4 bg-gray-50 rounded-lg">
                            No hay documentos adjuntos.
                        </div>

                        <div v-for="doc in documentsList" :key="doc.id"
                            class="border border-gray-200 rounded-xl p-5 hover:bg-gray-50 transition shadow-sm"
                            :class="{ 'bg-blue-50/50 ring-1 ring-[var(--institutional-primary)]/10': documentsState[doc.id]?.showPreview }"
                        >
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

                                <!-- Actions -->
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

                            <!-- Inline Preview (Premium) -->
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
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="w-full h-[55vh] sm:h-[65vh] lg:h-[600px] bg-white rounded-xl overflow-hidden border border-gray-300 shadow-inner relative">
                                        <div class="absolute inset-0 flex items-center justify-center text-gray-400 z-0 text-center">
                                            <div class="flex flex-col items-center gap-2">
                                                <svg class="w-10 h-10 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
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

                <!-- Rubric Evaluation (READ ONLY) -->
                <div v-if="!isEvaluatedByAdmin" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-[var(--institutional-primary)] px-4 sm:px-6 py-4 border-b border-gray-200 flex flex-col sm:flex-row justify-between sm:items-center gap-3 text-white">
                        <div>
                            <h2 class="font-bold text-lg">Resultados de Evaluación</h2>
                            <p class="text-blue-100 text-sm">Respuestas registradas</p>
                        </div>
                        <div class="text-left sm:text-right">
                            <p class="text-xs opacity-80 uppercase tracking-widest">Puntuación Final</p>
                            <p class="text-2xl sm:text-3xl font-bold">{{ Math.round(evaluation.score) }} <span class="text-base sm:text-lg font-normal">pts</span></p>
                        </div>
                    </div>
                    
                    <div class="p-4 sm:p-6 space-y-6 sm:space-y-8">
                        <div v-if="!rubric" class="text-center py-8 text-gray-500">
                            No se pudo cargar la rúbrica original.
                        </div>

                        <div v-else v-for="(question, qIndex) in rubric.questions" :key="question.id" class="border-b border-gray-100 pb-6 last:border-0 last:pb-0">
                            <div class="mb-3">
                                <span class="text-xs font-bold text-blue-600 uppercase tracking-wider mb-1 block">Criterio {{ qIndex + 1 }}</span>
                                <h3 class="text-base sm:text-lg font-medium text-gray-900">{{ question.text }}</h3>
                            </div>
                            
                            <div class="mt-3">
                                <div v-if="getSelectedOption(question)" 
                                    class="relative flex items-start sm:items-center p-3 sm:p-4 rounded-lg border border-blue-500 bg-blue-50"
                                >
                                    <div class="flex items-center h-5">
                                        <div class="h-4 w-4 rounded-full border border-blue-600 bg-blue-600 flex items-center justify-center">
                                            <div class="w-1.5 h-1.5 bg-white rounded-full"></div>
                                        </div>
                                    </div>
                                    <div class="ml-3 flex-1 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2">
                                        <span class="text-sm font-medium text-blue-900 break-words">
                                            {{ getSelectedOption(question).text }}
                                        </span>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ Math.round(getSelectedOption(question).score) }} pts
                                        </span>
                                    </div>
                                </div>
                                <div v-else class="p-4 rounded-lg border border-gray-200 bg-gray-50 text-gray-500 text-sm italic">
                                    No se seleccionó ninguna opción.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </EvaluatorLayout>
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
