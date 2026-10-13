<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { computed, ref } from 'vue';
import { 
    mdiFileDocumentMultiple, 
    mdiChevronRight,
    mdiMessageText
} from '@mdi/js';

const props = defineProps({
    evaluation: Object,
    application: Object,
    rubric: Object,
    teacher: Object,
});

// Since it's read only, answers are derived from the evaluation dictionary
const evaluateAnswers = computed(() => props.evaluation.answers || {});

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('es-ES', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
};

const getStatusLabel = (status) => {
    const labels = {
        pending: 'Pendiente',
        approved: 'Aprobada',
        rejected: 'Rechazada',
        expired: 'Expirada',
    };
    return labels[status] || status;
};

// Use the explicit score property of the evaluation
const currentScore = computed(() => {
    return Math.round(Number(props.evaluation.score) || 0);
});
</script>

<template>
    <AdminLayout>
        <Head title="Respuestas del Evaluador" />

        <div class="space-y-6">
            <!-- Header with Breadcrumbs -->
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="w-full md:w-auto">
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Respuestas del Evaluador</h1>
                    <div class="flex flex-wrap items-center gap-2 mt-2 text-sm">
                        <svg viewBox="0 0 24 24" class="w-4 h-4 flex-shrink-0" style="fill: #1B396A;">
                            <path :d="mdiFileDocumentMultiple"/>
                        </svg>
                        <Link :href="route('admin.applications.index')" class="text-gray-700 font-medium hover:text-[var(--institutional-primary)]">Solicitudes</Link>
                         <svg viewBox="0 0 24 24" class="w-4 h-4 text-gray-400" style="fill: currentColor">
                            <path :d="mdiChevronRight"/>
                        </svg>
                        <Link :href="route('admin.applications.show', application.id || application.data?.id)" class="text-gray-700 font-medium hover:text-[var(--institutional-primary)]">Detalles de solicitud</Link>
                         <svg viewBox="0 0 24 24" class="w-4 h-4 text-gray-400" style="fill: currentColor">
                            <path :d="mdiChevronRight"/>
                        </svg>
                        <span class="text-gray-900 font-semibold">Respuestas</span>
                    </div>
                </div>
                <Link :href="route('admin.applications.show', application.id || application.data?.id)" class="w-full md:w-auto justify-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition flex items-center gap-2 font-medium">
                    <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="currentColor">
                        <path d="m313-440 224 224-57 56-320-320 320-320 57 56-224 224h487v80H313Z"/>
                    </svg>
                    Regresar
                </Link>
            </div>

            <div class="space-y-6">
                <!-- Info Header -->
                <div class="bg-white p-5 sm:p-8 rounded-xl shadow-sm border border-gray-200">
                    <div class="flex flex-col md:flex-row md:items-center justify-between border-b border-gray-100 pb-4 mb-6 gap-2">
                        <h2 class="text-lg font-bold text-gray-900">Datos de la Evaluación</h2>
                         <!-- Badge Status -->
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-white text-xs font-bold border border-gray-100 self-start md:self-auto"
                            :class="{
                                'text-green-700 shadow-[0_2px_10px_rgba(21,128,61,0.15)]': evaluation.status === 'approved',
                                'text-red-700 shadow-[0_2px_10px_rgba(185,28,28,0.15)]': evaluation.status === 'rejected',
                                'text-yellow-700 shadow-[0_2px_10px_rgba(234,179,8,0.15)]': evaluation.status === 'pending',
                                'text-gray-700 shadow-[0_2px_10px_rgba(55,65,81,0.15)]': evaluation.status === 'expired',
                            }">
                            <span 
                                class="w-2 h-2 rounded-full"
                                :class="{
                                    'bg-green-500': evaluation.status === 'approved',
                                    'bg-red-500': evaluation.status === 'rejected',
                                    'bg-yellow-500': evaluation.status === 'pending',
                                    'bg-gray-500': evaluation.status === 'expired'
                                }"
                            ></span>
                            {{ getStatusLabel(evaluation.status) }}
                        </span>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                        <div>
                            <h3 class="text-xs font-bold text-[var(--institutional-primary)] uppercase tracking-wider mb-1">Evaluador Asignado</h3>
                            <p class="text-md font-medium text-gray-900">{{ evaluation.evaluator?.name || 'Sistema' }}</p>
                        </div>
                        <div>
                            <h3 class="text-xs font-bold text-[var(--institutional-primary)] uppercase tracking-wider mb-1">Docente</h3>
                            <p class="text-md font-medium text-gray-900">{{ teacher?.name || 'Sin docente' }}</p>
                        </div>
                        <div>
                            <h3 class="text-xs font-bold text-[var(--institutional-primary)] uppercase tracking-wider mb-1">Instituto</h3>
                            <p class="text-md font-medium text-gray-900">{{ teacher?.institution?.name || application.campus || application.data?.campus || 'N/A' }}</p>
                        </div>
                        <div>
                            <h3 class="text-xs font-bold text-[var(--institutional-primary)] uppercase tracking-wider mb-1">Tipo de Plaza</h3>
                            <p class="text-md font-medium text-gray-900">{{ application.position_full_type || application.data?.position_full_type || 'No especificado' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Motivo de Rechazo -->
                <div v-if="evaluation.status === 'rejected' && evaluation.comment"
                    class="relative flex items-start gap-4 px-5 py-4 rounded-lg bg-white shadow-sm border border-gray-100"
                    style="border-left: 5px solid #EF4444;"
                >
                    <div class="p-2 flex-shrink-0">
                        <svg viewBox="0 0 24 24" class="w-7 h-7" style="fill: #EF4444;"><path :d="mdiMessageText"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider mb-0.5" style="color: #EF4444;">Motivo de Rechazo</p>
                        <h3 class="font-bold text-gray-900 text-base">Comentario del Evaluador</h3>
                        <p class="text-sm text-gray-900 mt-0.5 italic">"{{ evaluation.comment }}"</p>
                    </div>
                </div>

                <!-- Main Content Grid -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-[var(--institutional-primary)] px-4 sm:px-6 py-4 border-b border-gray-200 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 text-white">
                        <div>
                            <h2 class="font-bold text-lg">Criterios Seleccionados</h2>
                            <p class="text-blue-100 text-sm">Vista solo lectura de las calificaciones emitidas</p>
                        </div>
                        <div class="text-left sm:text-right">
                            <p class="text-xs opacity-80 uppercase tracking-widest">Puntuación Total</p>
                            <p class="text-2xl sm:text-3xl font-bold">{{ currentScore }} <span class="text-base sm:text-lg font-normal">pts</span></p>
                        </div>
                    </div>
                    
                    <div class="p-4 sm:p-6 space-y-8">
                        <div v-if="!rubric" class="text-center py-8 text-gray-500">
                            No hay rúbrica configurada.
                        </div>

                        <div v-else v-for="(question, qIndex) in rubric.questions" :key="question.id" class="border-b border-gray-100 pb-6 last:border-0 last:pb-0">
                            <div class="mb-3">
                                <span class="text-xs font-bold text-blue-600 uppercase tracking-wider mb-1 block">Criterio {{ qIndex + 1 }}</span>
                                <h3 class="text-lg font-medium text-gray-900">{{ question.text }}</h3>
                            </div>
                            
                            <div class="mt-3 space-y-3">
                                <template v-for="option in question.options" :key="option.id">
                                    <div v-if="evaluateAnswers[question.id]?.option_id == option.id" 
                                        class="relative flex items-center p-4 rounded-lg border transition-all duration-200 border-blue-500 bg-blue-50 ring-1 ring-blue-500"
                                    >
                                        <div class="flex items-center h-5">
                                            <svg class="h-5 w-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                        <div class="ml-3 flex-1 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2">
                                            <span class="text-sm font-bold text-blue-900">
                                                {{ option.text }}
                                            </span>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                                                {{ Math.round(Number(option.score)) }} pts
                                            </span>
                                        </div>
                                    </div>
                                </template>
                                <div v-if="!evaluateAnswers[question.id]" class="p-4 rounded-lg border border-red-200 bg-red-50 text-red-700 text-sm italic">
                                    El evaluador no registró respuesta para este criterio.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
