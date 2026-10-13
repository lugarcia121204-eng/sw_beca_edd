<script setup>
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import VueSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';
import { ref, computed, watch } from 'vue';
import { 
    mdiFileDocumentMultiple,
    mdiAccountPlus, 
    mdiChevronRight, 
    mdiDelete,
    mdiAccountSchool
} from '@mdi/js';
import { alertaExito, alertaError, alertaCargando, cerrarAlerta } from '@/utils/alerts.js';

const props = defineProps({
    application: Object,
    evaluators: Array,
});

const selectedEvaluators = ref([]);

// Alias para la tabla (misma referencia)
const selectedEvaluatorsObjects = computed(() => selectedEvaluators.value);

const unwrap = (obj) => {
    return obj && obj.data ? obj.data : obj;
};

const getUser = () => unwrap(props.application.user);
const getConvocatoria = () => unwrap(props.application.announcement);

const availableEvaluators = computed(() => {
    const selectedIds = selectedEvaluators.value.map(e => e.id);
    const applicantId = getUser()?.id;
    return props.evaluators.filter(e => !selectedIds.includes(e.id) && e.id !== applicantId);
});

const form = useForm({
    application_id: props.application.id,
    evaluator_ids: [],
});

// Update form when selection changes
watch(selectedEvaluators, (newVal) => {
    form.evaluator_ids = newVal.map(e => e.id);
}, { deep: true });

const removeEvaluator = (id) => {
    selectedEvaluators.value = selectedEvaluators.value.filter(e => e.id !== id);
};

const submit = () => {
    if (selectedEvaluators.value.length === 0) return;
    // Limpiar errores previos
    form.clearErrors();
    alertaCargando('Asignando', 'Por favor espera...');
    form.post(route('admin.applications.assign'), {
        preserveScroll: true,
        onSuccess: () => {
            cerrarAlerta();
            alertaExito('Asignación Completada', 'Los evaluadores han sido asignados correctamente.');
        },
        onError: () => {
            cerrarAlerta();
            alertaError('Error', 'No se pudo completar la asignación. Por favor verifica los datos.');
        }
    });
};

const getStatusLabel = (status) => {
    const labels = {
        'pending': 'Pendiente',
        'approved': 'Aprobada',
        'rejected': 'Rechazada',
    };
    return labels[status] || status;
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

const filterEvaluators = (options, search) => {
    return options.filter(option => {
        const label = option.name.toLowerCase();
        const institution = (option.institution?.name || '').toLowerCase();
        const query = search.toLowerCase();
        return label.includes(query) || institution.includes(query);
    });
};

const isStageEvaluacion = computed(() => {
    return props.application?.announcement?.current_stage === 'evaluacion';
});
</script>

<template>
    <Head title="Asignar Evaluador" />

    <AdminLayout>
        <div class="mx-6 my-4 rounded-lg bg-blue-50 p-4 text-blue-900">Etapa: <strong>{{ {local:'Local',state:'Estatal',national:'Nacional',completed:'Concluida'}[application.review_stage] }}</strong>. Cada nivel registra una revisión independiente.</div>

        <div class="space-y-6 max-w-7xl mx-auto">
            <!-- Header with Breadcrumbs -->
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="w-full md:w-auto">
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Asignar Evaluador</h1>
                    <div class="flex items-center gap-2 mt-2 text-sm">
                        <svg viewBox="0 0 24 24" class="w-4 h-4 flex-shrink-0" style="fill: #1B396A;">
                            <path :d="mdiFileDocumentMultiple"/>
                        </svg>
                        <Link :href="route('admin.applications.index')" class="text-gray-700 font-medium hover:text-[var(--institutional-primary)]">Solicitudes</Link>
                         <svg xmlns="http://www.w3.org/2000/svg" height="12px" viewBox="0 -960 960 960" width="12px" fill="#9CA3AF">
                            <path d="m321-80-71-71 329-329-329-329 71-71 400 400L321-80Z"/>
                        </svg>
                        <span class="text-gray-900 font-semibold">Asignar Evaluador</span>
                    </div>
                </div>
                <Link :href="route('admin.applications.index')" class="w-full md:w-auto justify-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition flex items-center gap-2 font-medium">
                    <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="currentColor">
                        <path d="m313-440 224 224-57 56-320-320 320-320 57 56-224 224h487v80H313Z"/>
                    </svg>
                    Regresar
                </Link>
            </div>

            <!-- Solicitud Info Card -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 relative">
                 <div class="flex flex-col md:flex-row md:items-center justify-between border-b border-gray-100 pb-4 mb-6 gap-2 md:gap-4">
                     <!-- Badge Estado -->
                     <span 
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-white text-xs font-bold shadow-sm border border-gray-100 self-start md:self-auto md:order-last"
                        :class="{
                            'text-green-700': application.status === 'approved',
                            'text-red-700': application.status === 'rejected',
                            'text-yellow-700': application.status === 'pending'
                        }"
                    >
                        <span 
                            class="w-2.5 h-2.5 rounded-full"
                            :class="{
                                'bg-green-500': application.status === 'approved',
                                'bg-red-500': application.status === 'rejected',
                                'bg-yellow-500': application.status === 'pending'
                            }"
                        ></span>
                        {{ getStatusLabel(application.status) }}
                    </span>
                    <h2 class="text-lg font-bold text-gray-900 md:order-first">Información General</h2>
                 </div>
                 
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                    <div>
                        <h3 class="text-xs uppercase text-gray-500 font-semibold mb-1">Profesor</h3>
                        <p class="text-md font-medium text-gray-900">{{ getUser()?.name || 'Completar datos' }}</p>
                    </div>
                    
                    <div>
                        <h3 class="text-xs uppercase text-gray-500 font-semibold mb-1">Institución</h3>
                        <p class="text-md font-medium text-gray-900">{{ application.campus || getUser()?.institucion?.name || 'N/A' }}</p>
                    </div>
                    
                    <div>
                        <h3 class="text-xs uppercase text-gray-500 font-semibold mb-1">Convocatoria</h3>
                        <p class="text-md font-medium text-gray-900">{{ getConvocatoria()?.name || 'General' }}</p>
                    </div>

                    <div>
                        <h3 class="text-xs uppercase text-gray-500 font-semibold mb-1">Área de Procedencia</h3>
                        <p class="text-md font-medium text-gray-900">{{ getUser()?.priority_area?.name || 'No registrada' }}</p>
                    </div>

                    <div>
                        <h3 class="text-xs uppercase text-gray-500 font-semibold mb-1">Subárea</h3>
                        <p class="text-md font-medium text-gray-900">{{ getUser()?.sub_area?.name || 'No registrada' }}</p>
                    </div>

                     <div>
                        <h3 class="text-xs uppercase text-gray-500 font-semibold mb-1">Fecha de Solicitud</h3>
                        <p class="text-md font-medium text-gray-900">{{ formatDate(application.created_at) }}</p>
                    </div>

                    <div>
                        <h3 class="text-xs uppercase text-gray-500 font-semibold mb-1">Tipo de Plaza</h3>
                        <p class="text-md font-medium text-gray-900">{{ application.position_full_type || 'No especificado' }}</p>
                    </div>
                </div>
            </div>

            <!-- Assignment Card -->
             <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="border-b border-gray-100 pb-4 mb-6">
                    <h2 class="text-lg font-bold text-gray-900">Seleccionar Evaluador</h2>
                    <p class="text-sm text-gray-500">Busque y seleccione los evaluadores que revisarán esta solicitud.</p>
                </div>
                
                <div v-if="!isStageEvaluacion" class="relative flex items-center gap-4 px-5 py-4 rounded-lg bg-white shadow-sm border border-gray-100" style="border-left: 5px solid #C9A800">
                    <div class="flex-shrink-0" style="color: #C9A800">
                        <svg viewBox="0 0 24 24" class="w-6 h-6" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-[10px] uppercase font-bold tracking-widest opacity-60 mb-0.5" style="color: #C9A800">Acción No Permitida</span>
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-bold leading-tight text-gray-800">Etapa actual: <strong>{{ application.announcement.current_stage || 'Desconocida' }}</strong></span>
                            <p class="text-[13px] text-gray-600 leading-snug">Solo se pueden asignar evaluadores cuando la convocatoria se encuentra activamente en la etapa de <strong>Evaluación</strong>.</p>
                        </div>
                    </div>
                </div>

                <form v-else @submit.prevent="submit">
                    <div class="space-y-8">
                        <!-- Dropdown -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-900 mb-2">Buscar Evaluador</label>
                            <VueSelect
                                v-model="selectedEvaluators"
                                :options="availableEvaluators"
                                :filter="filterEvaluators"
                                label="name"
                                multiple
                                placeholder="Escribe para buscar..."
                                class="vue-select-custom"
                            >
                                <template #option="{ name, institution }">
                                    <div class="flex flex-col py-1">
                                        <span class="font-medium text-gray-900 text-sm">{{ name }}</span>
                                        <span class="text-xs text-gray-500">{{ institution?.name || 'Sin institución' }}</span>
                                    </div>
                                </template>
                            </VueSelect>
                        </div>

                        <!-- Table of Selected Evaluators -->
                        <div class="overflow-x-auto border border-gray-200 rounded-lg">
                            <table class="min-w-[720px] w-full divide-y divide-gray-200">
                                <thead class="bg-[var(--institutional-primary)]">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider w-16">
                                            ID
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                                            Nombre
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                                            Institución
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-white uppercase tracking-wider w-24">
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="(evaluator, index) in selectedEvaluatorsObjects" :key="evaluator.id" class="hover:bg-gray-50 transition">
                                        <td class="px-3 md:px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            {{ index + 1 }}
                                        </td>
                                        <td class="px-3 md:px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ evaluator.name }}
                                        </td>
                                        <td class="px-3 md:px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            {{ evaluator.institution?.name || 'N/A' }}
                                        </td>
                                         <td class="px-3 md:px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button 
                                                type="button" 
                                                @click="removeEvaluator(evaluator.id)"
                                                class="p-2 text-red-600 border border-red-600 rounded-full hover:bg-red-600 hover:text-white transition group cursor-pointer"
                                                title="Eliminar"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="currentColor">
                                                    <path d="M280-120q-33 0-56.5-23.5T200-200v-520h-40v-80h200v-40h240v40h200v80h-40v520q0 33-23.5 56.5T680-120H280Zm400-600H280v520h400v-520ZM360-280h80v-360h-80v360Zm160 0h80v-360h-80v360ZM280-720v520-520Z"/>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="selectedEvaluatorsObjects.length === 0">
                                        <td colspan="4" class="px-6 py-10 text-center text-sm text-gray-500 bg-gray-50">
                                            <div class="flex flex-col items-center justify-center gap-2">
                                                <svg viewBox="0 0 24 24" class="w-8 h-8 text-gray-300" fill="currentColor"><path :d="mdiAccountSchool"/></svg>
                                                <span class="italic">No hay evaluadores seleccionados. Añade uno desde el buscador.</span>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-3 pt-2">
                            <Link :href="route('admin.applications.index')" class="w-full sm:w-auto px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition text-center">
                                Cancelar
                            </Link>
                            <button 
                                type="submit" 
                                class="w-full sm:w-auto px-6 py-2.5 bg-[var(--institutional-primary)] text-white rounded-lg font-bold uppercase text-[11px] tracking-wider hover:bg-[var(--institutional-primary-dark)] transition shadow-md disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 cursor-pointer transform hover:-translate-y-0.5 active:translate-y-0"
                                :disabled="form.processing || selectedEvaluators.length === 0"
                            >
                                <svg viewBox="0 0 24 24" class="w-4 h-4" fill="currentColor"><path :d="mdiAccountPlus"/></svg>
                                Asignar Evaluador(es)
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.vue-select-custom :deep(.vs__dropdown-toggle) {
    background: #F3F4F6;
    border: none;
    border-bottom: 2px solid #D1D5DB;
    border-radius: 0.5rem;
    padding: 6px;
    min-height: 45px;
    transition: all 0.2s;
}

.vue-select-custom :deep(.vs__dropdown-toggle):hover {
    border-bottom-color: rgba(27, 57, 106, 0.5);
}

.vue-select-custom :deep(.vs--open .vs__dropdown-toggle) {
    background: #EFF6FF;
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
    background-color: #ffffff;
    color: #1e3a8a;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    padding: 3px 10px;
    margin: 2px;
    font-size: 0.85rem;
    font-weight: 600;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
}

.vue-select-custom :deep(.vs__actions) {
    padding-right: 8px;
}

.vue-select-custom :deep(.vs__clear),
.vue-select-custom :deep(.vs__open-indicator) {
    fill: #1B396A;
}

.vue-select-custom :deep(.vs__dropdown-menu) {
    border: 1px solid #E5E7EB;
    border-radius: 0.5rem;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    margin-top: 4px;
}

.vue-select-custom :deep(.vs__dropdown-option) {
    padding: 0.625rem 0.75rem;
    color: #374151;
    font-size: 0.875rem;
}

.vue-select-custom :deep(.vs__dropdown-option--highlight) {
    background: #1B396A;
    color: white;
}

.vue-select-custom :deep(.vs__dropdown-option--highlight) * {
    color: white !important;
}
</style>
