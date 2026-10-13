<script setup>
import AdminLayout from '@/layouts/AdminLayout.vue';
import { usePage, router, Head, Link } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { alertaPregunta, alertaExito, alertaError, alertaCargando, cerrarAlerta, alertaConfirmacionEscrita } from '@/utils/alerts.js';
import RejectModal from './RejectModal.vue';
import { 
    mdiEye, 
    mdiEyeOff, 
    mdiDownload, 
    mdiFileDocumentMultiple,
    mdiCheckCircle,
    mdiCloseCircle,
    mdiClockOutline,
    mdiChevronRight
} from '@mdi/js';

const props = defineProps({
    application: Object,
});

const page = usePage();
const rejectModalOpen = ref(false);
const processing = ref(false);
const documentsState = ref({});
const expandedComments = ref({});

const toggleComment = (id) => {
    expandedComments.value[id] = !expandedComments.value[id];
};

// Watch for flash messages
watch(() => page.props.flash?.success, (successMessage) => {
    if (successMessage) {
        alertaExito('¡Éxito!', successMessage);
    }
}, { immediate: true });

const unwrap = (obj) => {
    return obj && obj.data ? obj.data : obj;
};

const getUser = () => unwrap(props.application.user);
const getConvocatoria = () => unwrap(props.application.announcement);
const getDocuments = () => {
    const docs = props.application.documents;
    const list = Array.isArray(docs) ? docs : (docs?.data || []);
    return list.map(d => unwrap(d));
};

const getStatusLabel = (status) => {
    const labels = {
        pending: 'Pendiente',
        approved: 'Aprobado',
        rejected: 'Rechazado',
        expired: 'Expirada',
        evaluated_by_admin: 'Evaluada por Admin',
    };
    return labels[status] || status;
};

const getEvaluatorStatus = (evStatus, appStatus) => {
    if (evStatus === 'pending' && appStatus !== 'pending') {
        return 'evaluated_by_admin';
    }
    return evStatus;
};

const isValidVerdictStage = computed(() => {
    const stage = props.application?.announcement?.current_stage;
    return stage === 'evaluacion' || stage === 'resultados';
});

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('es-ES', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
};

const approveRequest = async () => {
    const confirmed = await alertaConfirmacionEscrita(
        '¿Aprobar solicitud?',
        'Esta acción aprobará la solicitud y notificará al docente. Esta operación no se puede deshacer.',
        'CONFIRMAR'
    );
    if (confirmed) {
        processing.value = true;
        alertaCargando('Procesando...', 'Aprobando la solicitud, por favor espere.');
        router.post(route('admin.applications.verdict', props.application.id), {
            status: 'approved'
        }, {
            preserveScroll: true,
            onSuccess: () => {
                cerrarAlerta();
                alertaExito('¡Aprobada!', 'La solicitud fue aprobada correctamente.');
            },
            onError: (errors) => {
                cerrarAlerta();
                const firstError = Object.values(errors)[0];
                alertaError('No se pudo aprobar', firstError || 'Inténtalo de nuevo.');
            },
            onFinish: () => processing.value = false
        });
    }
};

const rejectRequest = () => {
    rejectModalOpen.value = true;
};

const togglePreview = (docId) => {
    // Handle both direct ID and data wrapper
    const id = docId;
    if (!documentsState.value[id]) {
        documentsState.value[id] = { showPreview: false };
    }
    documentsState.value[id].showPreview = !documentsState.value[id].showPreview;
};

const getPreviewUrl = (doc) => {
    const id = doc.id || doc.data?.id;
    return route('catalog.admin.documents.stream', id);
};

const getDownloadUrl = (doc) => {
    const id = doc.id || doc.data?.id;
    return route('catalog.admin.documents.download', id);
};

const getFileIcon = (type) => {
    return 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5-3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z'; 
};
</script>

<template>
    <Head title="Detalles de Solicitud" />

    <AdminLayout>
        <div class="space-y-6">
            <!-- Header with Breadcrumbs -->
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="w-full md:w-auto">
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Detalles de la Solicitud</h1>
                    <div class="flex flex-wrap items-center gap-2 mt-2 text-sm">
                        <svg viewBox="0 0 24 24" class="w-4 h-4 flex-shrink-0" style="fill: #1B396A;">
                            <path :d="mdiFileDocumentMultiple"/>
                        </svg>
                        <Link :href="route('admin.applications.index')" class="text-gray-700 font-medium hover:text-[var(--institutional-primary)]">Solicitudes</Link>
                         <svg viewBox="0 0 24 24" class="w-4 h-4 text-gray-400" fill="currentColor">
                            <path :d="mdiChevronRight"/>
                        </svg>
                        <span class="text-gray-900 font-semibold">Detalles de Solicitud</span>
                    </div>
                </div>
                <Link :href="route('admin.applications.index')" class="w-full md:w-auto justify-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition flex items-center gap-2 font-medium">
                    <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="currentColor">
                        <path d="m313-440 224 224-57 56-320-320 320-320 57 56-224 224h487v80H313Z"/>
                    </svg>
                    Regresar
                </Link>
            </div>
                        <!-- Main Info Card -->
            <div class="bg-white p-4 sm:p-8 rounded-lg shadow-md border border-gray-200">
                <div class="flex flex-col md:flex-row md:items-center justify-between border-b border-gray-100 pb-4 mb-8 gap-2 md:gap-4">
                    <h3 class="text-xl font-bold text-gray-900">Información de la Solicitud</h3>
                     <span 
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-white text-xs font-bold shadow-sm border border-gray-100 self-start md:self-auto"
                        :class="{
                            'text-green-700': application.status === 'approved',
                            'text-red-700': application.status === 'rejected',
                            'text-yellow-700': application.status === 'pending'
                        }"
                    >
                        <span 
                            class="w-2.5 h-2.5 rounded-full"
                            :class="{
                                'bg-green-500 animate-pulse': application.status === 'approved',
                                'bg-red-500': application.status === 'rejected',
                                'bg-yellow-500': application.status === 'pending'
                            }"
                        ></span>
                        {{ getStatusLabel(application.status) }}
                    </span>
                </div>

                <!-- Info Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-y-6 sm:gap-y-8 gap-x-8 xl:gap-x-12 mb-10">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Profesor</p>
                        <p class="text-gray-900 font-medium text-base">{{ getUser()?.name || 'Usuario' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Área Prioritaria</p>
                        <p class="text-gray-900 font-medium text-base">{{ getUser()?.priority_area?.name || 'No asignado' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Institución</p>
                        <p class="text-gray-900 text-base leading-snug">{{ application.campus || getUser()?.institution?.name || 'No registrado' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Tipo de Beca</p>
                        <p class="text-gray-900 text-base">{{ getConvocatoria()?.name }}</p>
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
                         <p class="text-gray-900 text-base">{{ formatDate(application.created_at) }}</p>
                    </div>
                </div>

                <!-- Documentation -->
                <div class="border-t border-gray-200 pt-8">
                    <h3 class="text-lg font-bold text-gray-900 mb-6 font-bold">Documentación</h3>
                    
                    <div class="grid grid-cols-1 gap-4">
                        <div v-if="getDocuments().length === 0" class="text-gray-500 italic text-center py-8 bg-gray-50 rounded-xl border border-dashed border-gray-200">
                            No hay documentos adjuntos.
                        </div>

                        <div v-for="doc in getDocuments()" :key="doc.id" 
                            class="border border-gray-200 rounded-xl p-5 hover:bg-gray-50 transition shadow-sm"
                            :class="{ 'bg-blue-50/50 ring-1 ring-[var(--institutional-primary)]/10': documentsState[doc.id]?.showPreview }"
                        >
                            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 sm:gap-6">
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
                                <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto sm:justify-end flex-shrink-0">
                                    <button 
                                        v-if="doc.file_type === 'pdf'" 
                                        @click="togglePreview(doc.id)" 
                                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2 border border-[var(--institutional-primary)] text-[var(--institutional-primary)] rounded-lg font-bold transition cursor-pointer text-xs whitespace-nowrap shadow-sm hover:bg-[var(--institutional-primary)] hover:text-white"
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
                                    <div class="w-full h-[55vh] md:h-[600px] bg-white rounded-xl overflow-hidden border border-gray-300 shadow-inner relative">
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

            <!-- Evaluadores -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100">
                        <h2 class="text-lg font-bold text-gray-900">Evaluadores Asignados</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[920px] text-left">
                            <thead class="bg-[var(--institutional-primary)] text-white">
                                <tr>
                                <th class="px-6 py-3 text-xs uppercase font-semibold w-12 whitespace-nowrap">ID</th>
                                    <th class="px-6 py-3 text-xs uppercase font-semibold whitespace-nowrap">Nombre</th>
                                    <th class="px-6 py-3 text-xs uppercase font-semibold whitespace-nowrap">Comentarios</th>
                                    <th class="px-6 py-3 text-xs uppercase font-semibold text-right whitespace-nowrap">Estado</th>
                                    <th class="px-6 py-3 text-xs uppercase font-semibold text-center whitespace-nowrap">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="(ev, index) in application.evaluations" :key="ev.id">
                                    <td class="px-3 md:px-6 py-4 text-sm text-gray-500 whitespace-nowrap">{{ index + 1 }}<span class="block text-xs text-blue-800">{{ {local:'Local',state:'Estatal',national:'Nacional'}[ev.review_level] || 'Histórica' }}</span></td>
                                    <td class="px-3 md:px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap">{{ ev.evaluator?.name }}</td>
                                    <td class="px-3 md:px-6 py-4 text-sm whitespace-normal break-words max-w-xs">
                                        <div v-if="ev.comment" class="text-gray-800">
                                            <span v-if="!expandedComments[ev.id] && ev.comment.length > 100">
                                                {{ ev.comment.substring(0, 100) }}...
                                                <br>
                                                <button @click="toggleComment(ev.id)" class="text-[var(--institutional-primary)] hover:text-[var(--institutional-primary-dark)] font-semibold text-xs mt-1 cursor-pointer inline-flex items-center gap-1 focus:outline-none">
                                                    Ver más
                                                </button>
                                            </span>
                                            <span v-else>
                                                {{ ev.comment }}
                                                <br>
                                                <button v-if="ev.comment.length > 100" @click="toggleComment(ev.id)" class="text-[var(--institutional-primary)] hover:text-[var(--institutional-primary-dark)] font-semibold text-xs mt-1 cursor-pointer inline-flex items-center gap-1 focus:outline-none">
                                                    Ver menos
                                                </button>
                                            </span>
                                        </div>
                                        <span v-else class="text-gray-400 italic">Sin comentarios</span>
                                    </td>
                                    <td class="px-3 md:px-6 py-4 text-right whitespace-nowrap">
                                        <span 
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-white text-xs font-bold shadow-sm border border-gray-100"
                                            :class="{
                                                'text-green-700': getEvaluatorStatus(ev.status, application.status) === 'approved',
                                                'text-red-700': getEvaluatorStatus(ev.status, application.status) === 'rejected',
                                                'text-yellow-700': getEvaluatorStatus(ev.status, application.status) === 'pending',
                                                'text-gray-700': getEvaluatorStatus(ev.status, application.status) === 'expired',
                                                'text-purple-700': getEvaluatorStatus(ev.status, application.status) === 'evaluated_by_admin',
                                            }"
                                        >
                                            <span 
                                                class="w-2 h-2 rounded-full"
                                                :class="{
                                                    'bg-green-500': getEvaluatorStatus(ev.status, application.status) === 'approved',
                                                    'bg-red-500': getEvaluatorStatus(ev.status, application.status) === 'rejected',
                                                    'bg-yellow-500 animate-pulse': getEvaluatorStatus(ev.status, application.status) === 'pending',
                                                    'bg-gray-500': getEvaluatorStatus(ev.status, application.status) === 'expired',
                                                    'bg-purple-500': getEvaluatorStatus(ev.status, application.status) === 'evaluated_by_admin',
                                                }"
                                            ></span>
                                            {{ getStatusLabel(getEvaluatorStatus(ev.status, application.status)) }}
                                        </span>
                                    </td>
                                    <td class="px-3 md:px-6 py-4 text-center whitespace-nowrap">
                                        <Link v-if="ev.status !== 'pending' && ev.status !== 'expired'" :href="route('admin.applications.evaluation.show', { application: application.id, evaluation: ev.id })" 
                                            class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 border border-[var(--institutional-primary)] text-[var(--institutional-primary)] hover:bg-[var(--institutional-primary)] hover:text-white rounded-lg text-xs font-bold transition shadow-sm"
                                            title="Ver Respuestas de la Rúbrica"
                                        >
                                            <svg viewBox="0 0 24 24" class="w-4 h-4" fill="currentColor"><path :d="mdiEye"/></svg>
                                            Respuestas
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="!application.evaluations || application.evaluations.length === 0">
                                    <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500 italic">
                                        No se han asignado evaluadores aún.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                 <div class="rounded-lg bg-blue-50 p-4 text-blue-900" v-if="application.status === 'pending'">
                    Etapa de revisión: <strong>{{ application.review_stage_label }}</strong>.
                    El resultado se determina con las evaluaciones Local, Estatal y Nacional.
                 </div>
                <!-- If already decided, show status (Optional: removed to avoid duplication with top banner, or kept simple) -->
                 <div v-else class="bg-gray-50 p-4 rounded-lg text-center border border-gray-200">
                    <p class="text-gray-600 font-medium">Esta solicitud ya ha sido completada con estado: 
                        <span class="font-bold" :class="{'text-red-600': application.status === 'rejected', 'text-green-600': application.status === 'approved'}">
                            {{ getStatusLabel(application.status) }}
                        </span>
                    </p>
            </div>
        </div>

        <!-- Reject Modal -->
        <RejectModal
            :show="rejectModalOpen"
            :applicationId="application.id"
            @close="rejectModalOpen = false"
        />
    </AdminLayout>
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
