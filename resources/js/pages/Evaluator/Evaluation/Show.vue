<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import EvaluatorLayout from '@/layouts/EvaluatorLayout.vue';
import { alertaPregunta, alertaAdvertencia, alertaConfirmacionEscrita, alertaCargando, cerrarAlerta, alertaExito, alertaError } from '@/utils/alerts.js';
import { ref, computed } from 'vue';
import RejectModal from './RejectModal.vue';
import { 
    mdiHome, 
    mdiFileDocumentMultiple, 
    mdiCheckCircle, 
    mdiCloseCircle,
    mdiArrowLeft,
    mdiEye,
    mdiEyeOff,
    mdiDownload,
    mdiFileDocumentOutline,
    mdiChevronRight
} from '@mdi/js';

const props = defineProps({
    evaluation: Object,
    application: Object,
    rubric: Object,
    teacher: Object,
});

const form = useForm({
    status: '',
    score: 0,
    answers: {}, // { question_id: { option_id, score } }
    comment: '',
});

const rejectModalOpen = ref(false);

// Logic for Document Preview (copied/adapted from Admin)
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

const getDownloadUrl = (doc) => {
    // Assuming a download route exists or using the stream with download header?
    // If no specific download route, we might use stream. 
    // Checking previous code: Admin had 'catalog.admin.documents.download'.
    // Evaluator might not have a dedicated download route in the previous code snippet, 
    // but usually stream can be used or we can fallback to stream.
    // Let's use stream for now or check if a download route exists.
    // Given the previous code didn't have a download button, we'll assume stream is fine or add a download route later.
    // Actually, let's use the stream URL for download as a fallback.
    return route('evaluator.documents.stream', doc.id); 
};

const getFileIcon = (type) => {
    return 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5-3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z'; 
};

// Normalizar la lista de documentos
const documentsList = computed(() => {
    const docs = props.application.documents;
    if (!docs) return [];
    return Array.isArray(docs) ? docs : (docs.data || []);
});

// Computed Properties for Progress tracking
const totalQuestions = computed(() => {
    return props.rubric && props.rubric.questions ? props.rubric.questions.length : 0;
});

const answeredCount = computed(() => {
    return Object.keys(form.answers).filter(key => form.answers[key] !== null && form.answers[key].option_id).length;
});

const progressPercentage = computed(() => {
    if (totalQuestions.value === 0) return 0;
    return (answeredCount.value / totalQuestions.value) * 100;
});

// Calculate total score based on selected options
const currentScore = computed(() => {
    let total = 0;
    for (const key in form.answers) {
        if (form.answers[key]) {
            total += form.answers[key].score;
        }
    }
    return total;
});

// Update score in form when computed changes
const updateFormScore = () => {
    form.score = currentScore.value;
};

const handleOptionSelect = (questionId, option) => {
    form.answers[questionId] = {
        option_id: option.id,
        score: option.score
    };
    updateFormScore();
};

const isStageEvaluacion = computed(() => {
    return props.application?.announcement?.current_stage === 'evaluacion';
});

const isReadOnly = computed(() => {
    return props.evaluation.status !== 'pending' || !isStageEvaluacion.value;
});

// LocalStorage Draft Management
import { watch, onMounted } from 'vue';

// Key includes BOTH evaluation.id AND application.id so that:
// - The same evaluation resumed later restores the correct draft.
// - If the same evaluation.id is ever reused with a different application (edge case),
//   the key is different → clean slate automatically.
const draftKey = computed(() =>
    `evaluator_draft_eval${props.evaluation.id}_app${props.application?.id ?? 0}`
);

const hasDraft = ref(false); // true when a saved draft exists in localStorage

const saveDraft = (showMessage = false) => {
    if (isReadOnly.value) return;
    localStorage.setItem(draftKey.value, JSON.stringify(form.answers));
    hasDraft.value = Object.keys(form.answers).length > 0;
    if (showMessage) {
        alertaExito('Borrador guardado', 'Tus respuestas han sido guardadas temporalmente de forma exitosa.');
    }
};

const clearDraft = async () => {
    const confirmed = await alertaPregunta(
        '¿Empezar de nuevo?',
        'Se borrará el borrador guardado y la rúbrica quedará en blanco. Esta acción no se puede deshacer.'
    );
    if (!confirmed) return;
    localStorage.removeItem(draftKey.value);
    form.answers = {};
    updateFormScore();
    hasDraft.value = false;
};

const loadDraft = () => {
    if (isReadOnly.value) return;

    // Reset form first to prevent bleed between navigations
    form.answers = {};
    updateFormScore();
    hasDraft.value = false;

    // Autoguardado 
    const savedDraft = localStorage.getItem(draftKey.value);
    if (savedDraft) {
        try {
            const parsedAnswers = JSON.parse(savedDraft);
            if (Object.keys(parsedAnswers).length > 0) {
                const validAnswers = {};
                const validQuestionIds = props.rubric?.questions?.map(q => q.id.toString()) || [];

                for (const [qId, answerData] of Object.entries(parsedAnswers)) {
                    if (validQuestionIds.includes(qId.toString())) {
                        validAnswers[qId] = answerData;
                    }
                }

                form.answers = validAnswers;
                updateFormScore();
                hasDraft.value = Object.keys(validAnswers).length > 0;
            }
        } catch (e) {
            console.error('Error parsing evaluation draft from local storage:', e);
        }
    }
};

onMounted(() => {
    loadDraft();
});

watch(() => props.evaluation?.id, () => {
    loadDraft();
});

// Autosave silently whenever an answer is selected
watch(() => form.answers, () => {
    if (!isReadOnly.value) {
        localStorage.setItem(draftKey.value, JSON.stringify(form.answers));
        hasDraft.value = Object.keys(form.answers).length > 0;
    }
}, { deep: true });

const submitEvaluation = async (status) => {
    // Validation: Ensure all rubric questions are answered
    if (props.rubric && props.rubric.questions) {
        const total = props.rubric.questions.length;
        const answered = answeredCount.value;
        
        if (answered < total) {
            alertaAdvertencia('Rúbrica incompleta', 'Debes evaluar todos los criterios de la rúbrica antes de finalizar.');
            return;
        }
    }

    form.status = status;
    form.score = currentScore.value; // Ensure score is up to date

    if (status === 'approved') {
        const confirmed = await alertaConfirmacionEscrita(
            '¿Aceptar Solicitud?',
            `La solicitud será aprobada con una puntuación de ${currentScore.value} puntos. Esta operación no se puede deshacer.`,
            'CONFIRMAR'
        );
        if (confirmed) {
            alertaCargando('Procesando...', 'Guardando evaluación, por favor espere.');
            form.put(route('evaluator.evaluation.update', props.evaluation.id), {
                onSuccess: () => {
                    cerrarAlerta();
                    alertaExito('¡Aprobada!', 'La evaluación fue aprobada correctamente.');
                    localStorage.removeItem(draftKey.value);
                },
                onError: () => {
                    cerrarAlerta();
                    alertaError('Error', 'No se pudo guardar la evaluación. Inténtalo de nuevo.');
                },
            });
        }
    } else if (status === 'rejected') {
        rejectModalOpen.value = true;
    }
};

const onRejectionSuccess = () => {
    localStorage.removeItem(draftKey.value);
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
</script>

<template>
    <EvaluatorLayout>
        <div class="mx-6 my-4 rounded-lg bg-blue-50 p-4 text-blue-900">Etapa: <strong>{{ {local:'Local',state:'Estatal',national:'Nacional',completed:'Concluida'}[application.review_stage] }}</strong>. Cada nivel registra una revisión independiente.</div>

        <Head title="Evaluar Solicitud" />

        <div class="space-y-6">
            <!-- Header with Breadcrumbs -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="w-full sm:w-auto">
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Evaluación de Solicitud</h1>
                    <div class="flex items-center gap-2 mt-2 text-sm">
                        <svg viewBox="0 0 24 24" class="w-4 h-4 flex-shrink-0" style="fill: #1B396A;">
                            <path :d="mdiFileDocumentMultiple"/>
                        </svg>
                        <Link :href="route('evaluator.dashboard')" class="text-gray-700 font-medium hover:text-[var(--institutional-primary)]">Inicio</Link>
                         <svg viewBox="0 0 24 24" class="w-4 h-4 text-gray-400" style="fill: currentColor">
                            <path :d="mdiChevronRight"/>
                        </svg>
                        <span class="text-gray-900 font-semibold">Evaluación</span>
                    </div>
                </div>
                <Link :href="route('evaluator.dashboard')" class="w-full sm:w-auto justify-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition flex items-center gap-2 font-medium">
                    <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="currentColor">
                        <path d="m313-440 224 224-57 56-320-320 320-320 57 56-224 224h487v80H313Z"/>
                    </svg>
                    Regresar
                </Link>
            </div>

            <!-- Read-Only Banner -->
            <transition enter-active-class="transition duration-500 ease-out" enter-from-class="transform -translate-y-4 opacity-0" enter-to-class="transform translate-y-0 opacity-100">
                <div
                    v-if="isReadOnly"
                    class="relative flex items-center gap-4 px-5 py-4 rounded-lg bg-white shadow-sm border border-gray-100"
                    :style="{ borderLeft: `5px solid ${ evaluation.status === 'approved' ? '#10A558' : evaluation.status === 'rejected' ? '#6B7280' : '#C9A800' }` }"
                >
                    <div class="flex-shrink-0" :style="{ color: evaluation.status === 'approved' ? '#10A558' : evaluation.status === 'rejected' ? '#6B7280' : '#C9A800' }">
                        <svg v-if="evaluation.status === 'approved'" viewBox="0 0 24 24" class="w-6 h-6" style="fill: currentColor"><path :d="mdiCheckCircle"/></svg>
                        <svg v-else-if="evaluation.status === 'rejected'" viewBox="0 0 24 24" class="w-6 h-6" style="fill: currentColor"><path :d="mdiCloseCircle"/></svg>
                        <svg v-else viewBox="0 0 24 24" class="w-6 h-6" style="fill: currentColor"><path :d="mdiClockOutline"/></svg>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-[10px] uppercase font-bold tracking-widest opacity-60 mb-0.5" :style="{ color: evaluation.status === 'approved' ? '#10A558' : evaluation.status === 'rejected' ? '#6B7280' : '#C9A800' }">
                            Estado de Evaluación
                        </span>
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-bold leading-tight text-gray-800">
                                {{ evaluation.status === 'approved' ? 'Evaluación Aprobada' : evaluation.status === 'rejected' ? 'Evaluación Rechazada' : evaluation.status === 'expired' ? 'Evaluación Expirada' : 'Modo Solo Lectura' }}
                            </span>
                            <p class="text-[13px] text-gray-600 leading-snug" v-if="evaluation.status === 'approved'">
                                Esta solicitud ya fue evaluada y aprobada correctamente.
                            </p>
                            <p class="text-[13px] text-gray-600 leading-snug" v-else-if="evaluation.status === 'rejected'">
                                Esta solicitud ya fue evaluada y rechazada.
                            </p>
                            <p class="text-[13px] text-gray-600 leading-snug" v-else-if="evaluation.status === 'pending' && !isStageEvaluacion">
                                Esta solicitud no puede ser contestada porque la etapa regular de <strong>Evaluación</strong> no está activa.
                            </p>
                            <p class="text-[13px] text-gray-600 leading-snug" v-else>
                                El tiempo de evaluación ha expirado.
                            </p>
                        </div>
                    </div>
                </div>
            </transition>

            <!-- Content Container -->
            <div class="space-y-6">

                <!-- Información General (Applicant) -->
                <div class="bg-white p-4 sm:p-6 rounded-xl shadow-sm border border-gray-100 relative">
                     <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-gray-100 pb-4 mb-6 gap-2 sm:gap-4">
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
                <div class="bg-white p-4 sm:p-6 md:p-8 rounded-lg shadow-md border border-gray-200">
                    <h3 class="text-lg font-bold text-gray-900 mb-6">Documentación</h3>
                    
                    <div class="grid grid-cols-1 gap-4">
                        <div v-if="documentsList.length === 0" class="text-gray-500 italic text-center py-8 bg-gray-50 rounded-xl border border-dashed border-gray-200">
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
                                        <span class="font-bold text-gray-900 block text-base md:text-lg truncate" :title="doc.name || doc.nombre">{{ doc.name || doc.nombre }}</span>
                                        <p class="text-xs text-gray-500 font-medium uppercase tracking-wider mt-0.5">{{ doc.file_type === 'pdf' ? 'Documento PDF' : 'Archivo' }}</p>
                                    </div>
                                </div>
                                
                                <!-- Actions -->
                                <div class="flex items-center gap-3 w-full sm:w-auto justify-end flex-shrink-0">
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
                                            Vista Previa: {{ doc.name || doc.nombre }}
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

                <!-- Main Content Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Column Left: Rubric -->
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                            <div class="bg-[var(--institutional-primary)] px-6 py-4 border-b border-gray-200 flex justify-between items-center text-white">
                                <div>
                                    <h2 class="font-bold text-lg">Rúbrica de Evaluación</h2>
                                    <p class="text-blue-100 text-sm">Selecciona una opción para cada criterio</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs opacity-80 uppercase tracking-widest">Puntuación Total</p>
                                    <p class="text-3xl font-bold">{{ currentScore }} <span class="text-lg font-normal">pts</span></p>
                                </div>
                            </div>
                            
                            <div class="p-4 sm:p-6 space-y-8">
                                <div v-if="!rubric" class="text-center py-8 text-gray-500">
                                    No hay una rúbrica activa asignada. Contacte al administrador.
                                </div>

                                <div v-else v-for="(question, qIndex) in rubric.questions" :key="question.id" class="border-b border-gray-100 pb-6 last:border-0 last:pb-0">
                                    <div class="mb-3">
                                        <span class="text-xs font-bold text-blue-600 uppercase tracking-wider mb-1 block">Criterio {{ qIndex + 1 }}</span>
                                        <h3 class="text-lg font-medium text-gray-900">{{ question.text }}</h3>
                                    </div>
                                    
                                    <div class="mt-3 space-y-3">
                                        <div v-for="option in question.options" :key="option.id" 
                                            class="relative flex items-center p-4 cursor-pointer rounded-lg border transition-all duration-200 hover:bg-gray-50"
                                            :class="{ 
                                                'border-blue-500 bg-blue-50 ring-1 ring-blue-500': form.answers[question.id]?.option_id === option.id,
                                                'border-gray-200': form.answers[question.id]?.option_id !== option.id
                                            }"
                                            @click="!isReadOnly && handleOptionSelect(question.id, option)"
                                        >
                                            <div class="flex items-center h-5">
                                                <input 
                                                    type="radio" 
                                                    :name="'question_' + question.id" 
                                                    :value="option.id"
                                                    :checked="form.answers[question.id]?.option_id === option.id"
                                                    :disabled="isReadOnly"
                                                    class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500 disabled:opacity-50"
                                                />
                                            </div>
                                            <div class="ml-3 flex-1 flex justify-between items-center">
                                                <span class="text-sm font-medium text-gray-900" 
                                                    :class="{'text-blue-900': form.answers[question.id]?.option_id === option.id}"
                                                >
                                                    {{ option.text }}
                                                </span>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800"
                                                    :class="{'bg-blue-100 text-blue-800': form.answers[question.id]?.option_id === option.id}"
                                                >
                                                    {{ option.score }} pts
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Column Right: Progress Summary & Actions -->
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-xl shadow-md border border-gray-200 p-4 sm:p-6 sticky top-6">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">Progreso de Evaluación</h3>
                            
                            <!-- Progress Bar -->
                            <div class="space-y-3 mb-6" v-if="rubric">
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-gray-600">Criterios evaluados:</span>
                                    <span class="font-bold text-gray-900">
                                        {{ answeredCount }} / {{ totalQuestions }}
                                    </span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                    <div class="bg-[var(--institutional-primary)] h-2.5 rounded-full transition-all duration-500" :style="{ width: progressPercentage + '%' }"></div>
                                </div>
                            </div>
                            
                            <!-- Guardado Manual (Solo si está pending) -->
                            <div v-if="!isReadOnly" class="mb-6">
                                <button 
                                    type="button" 
                                    @click="saveDraft(true)"
                                    class="w-full flex justify-center py-2.5 px-4 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none transition gap-2 items-center cursor-pointer"
                                >
                                    <svg viewBox="0 0 24 24" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                                        <polyline points="7 3 7 8 15 8"></polyline>
                                    </svg>
                                    Guardar Borrador
                                </button>

                                <!-- Empezar de nuevo: solo visible si hay borrador guardado -->
                                <button
                                    v-if="hasDraft"
                                    type="button"
                                    @click="clearDraft()"
                                    class="mt-2 w-full flex justify-center py-2 px-4 border border-red-200 rounded-lg text-xs font-medium text-red-500 bg-red-50 hover:bg-red-100 transition gap-2 items-center cursor-pointer"
                                >
                                    <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="1 4 1 10 7 10"></polyline>
                                        <path d="M3.51 15a9 9 0 1 0 .49-3.09"></path>
                                    </svg>
                                    Empezar de nuevo (limpiar borrador)
                                </button>

                                <p class="text-xs text-gray-500 mt-2 text-center">
                                    Tus selecciones se guardan automáticamente, pero puedes asegurar el guardado manualmente.
                                </p>
                            </div>

                            <hr class="my-6 border-gray-200" v-if="!isReadOnly">

                            <!-- Submit Actions -->
                            <div v-if="!isReadOnly" class="flex flex-col gap-3">
                                <p v-if="!rubric" class="text-red-500 text-sm mb-2 text-center">No se puede evaluar sin rúbrica.</p>
                                
                                <button 
                                    type="button" 
                                    @click="submitEvaluation('rejected')"
                                    :disabled="form.processing"
                                    class="w-full px-4 py-2.5 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 transition-colors flex items-center justify-center gap-2 uppercase tracking-wide cursor-pointer"
                                > 
                                    <svg viewBox="0 0 24 24" class="w-5 h-5" style="fill: currentColor"><path :d="mdiCloseCircle"/></svg>
                                    Rechazar Solicitud
                                </button>

                                <button 
                                    type="button" 
                                    @click="submitEvaluation('approved')"
                                    :disabled="form.processing || !rubric"
                                    class="w-full px-4 py-2.5 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-[var(--institutional-primary)] hover:bg-[var(--institutional-primary-dark)] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--institutional-primary)] disabled:opacity-50 transition-colors flex items-center justify-center gap-2 uppercase tracking-wide cursor-pointer"
                                >
                                    <svg viewBox="0 0 24 24" class="w-5 h-5" style="fill: currentColor"><path :d="mdiCheckCircle"/></svg>
                                    Aceptar Solicitud
                                </button>
                            </div>
                            <!-- Si ya terminó -->
                            <div v-else class="text-center py-4 rounded-lg text-sm font-medium border"
                                 :class="[
                                    evaluation.status === 'pending' ? 'text-gray-700 bg-gray-50 border-gray-200' : 'text-green-700 bg-green-50 border-green-200'
                                 ]"
                            >
                                <span v-if="evaluation.status === 'pending'">Evaluación suspendida (fuera de etapa).</span>
                                <span v-else>Evaluación Finalizada.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <RejectModal 
                :show="rejectModalOpen" 
                :evaluationId="evaluation.id"
                :score="currentScore"
                :answers="form.answers"
                @close="rejectModalOpen = false"
                @success="onRejectionSuccess"
            />
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
