<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import EvaluatorLayout from '@/layouts/EvaluatorLayout.vue';
import Pagination from '@/Shared/Pagination.vue';
import { ref, watch } from 'vue';
import { debounce } from 'lodash';
import VueSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';
import { mdiSchool, mdiCheckCircle, mdiCloseCircle, mdiFileDocumentMultiple } from '@mdi/js';

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({ approved: 0, rejected: 0, evaluadas: 0, total: 0, pendientes: 0 })
    },
    applications: Object,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const rows = ref(props.filters?.rows || 10);
const statusFilter = ref(props.filters?.status || null);

const rowOptions = [
    { label: '5 Registros', value: 5 },
    { label: '10 Registros', value: 10 },
    { label: '25 Registros', value: 25 },
    { label: '50 Registros', value: 50 },
];

const statusOptions = [
    { label: 'Aprobadas', value: 'approved' },
    { label: 'Rechazadas', value: 'rejected' },
    { label: 'Expiradas', value: 'expired' },
];

const applyFilters = () => {
    router.get(route('evaluator.evaluations.index'), {
        search: search.value,
        rows: rows.value,
        status: statusFilter.value,
    }, { preserveState: true, replace: true, preserveScroll: true });
};

const onSearch = debounce((value) => {
    router.get(route('evaluator.evaluations.index'), { 
        search: value,
        rows: rows.value,
        status: statusFilter.value,
    }, { preserveState: true, replace: true });
}, 500);

const cleanFilters = () => {
    search.value = '';
    rows.value = 10;
    statusFilter.value = null;
    router.get(route('evaluator.evaluations.index'), {}, { preserveState: true, replace: true });
};

const onRowsChange = () => applyFilters();
const onStatusChange = () => applyFilters();

watch(search, (value) => {
    onSearch(value);
});

const getStatusLabel = (status) => {
    const labels = {
        pending: 'Pendiente',
        approved: 'Aprobada',
        rejected: 'Rechazada',
        expired: 'Expirada',
        evaluated_by_admin: 'Evaluada por Admin',
    };
    return labels[status] || status;
};
</script>

<template>
    <EvaluatorLayout>
        <Head title="Historial de Evaluaciones" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Historial de Evaluaciones</h1>
                    <div class="flex items-center gap-2 mt-2 text-sm">
                        <svg viewBox="0 0 24 24" class="w-4 h-4 flex-shrink-0" style="fill: #1B396A;">
                            <path :d="mdiSchool"/>
                        </svg>
                        <span class="text-gray-900 font-semibold">Evaluaciones</span>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                <!-- Aprobadas -->
                <div class="bg-white rounded-lg shadow-md border-l-4 border-[#10B981] p-4 hover:shadow-lg transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Aprobadas</p>
                            <p class="text-2xl font-bold text-gray-900">{{ stats?.aprobadas ?? 0 }}</p>
                        </div>
                        <div class="p-2">
                             <svg viewBox="0 0 24 24" class="w-8 h-8 text-[#10B981]" style="fill: currentColor"><path :d="mdiCheckCircle"/></svg>
                        </div>
                    </div>
                </div>

                <!-- Rechazadas -->
                <div class="bg-white rounded-lg shadow-md border-l-4 border-[#EF4444] p-4 hover:shadow-lg transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Rechazadas</p>
                            <p class="text-2xl font-bold text-gray-900">{{ stats?.rechazadas ?? 0 }}</p>
                        </div>
                        <div class="p-2">
                             <svg viewBox="0 0 24 24" class="w-8 h-8 text-[#EF4444]" style="fill: currentColor"><path :d="mdiCloseCircle"/></svg>
                        </div>
                    </div>
                </div>

                <!-- Total Evaluadas -->
                <div class="bg-white rounded-lg shadow-md border-l-4 border-[var(--institutional-primary)] p-4 hover:shadow-lg transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Total Completadas</p>
                            <p class="text-2xl font-bold text-gray-900">{{ stats?.evaluadas ?? 0 }}</p>
                        </div>
                        <div class="p-2">
                             <svg viewBox="0 0 24 24" class="w-8 h-8 text-[var(--institutional-primary)]" style="fill: currentColor"><path :d="mdiFileDocumentMultiple"/></svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter and Table Section -->
            <div class="bg-white rounded-lg shadow-md border border-gray-200 p-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
                    <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#374151">
                            <path d="M440-160q-17 0-28.5-11.5T400-200v-240L168-736q-15-20-4.5-42t36.5-22h560q26 0 36.5 22t-4.5 42L560-440v240q0 17-11.5 28.5T520-160h-80Zm40-308 198-252H282l198 252Zm0 0Z"/>
                        </svg>
                        <h2 class="text-xl font-semibold text-gray-800">Filtro de Búsqueda</h2>
                    </div>
                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <button @click="cleanFilters" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 flex items-center gap-2 text-sm font-medium transition cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="currentColor">
                                <path d="M400-240v-80h240v80H400Zm-158 0L15-467l57-57 170 170 366-366 57 57-423 423Zm318-160v-80h240v80H560Zm160-160v-80h240v80H720Z"/>
                            </svg>
                            Limpiar Filtros
                        </button>
                    </div>
                </div>

                <div class="text-sm text-gray-500 mb-4">Buscar por convocatoria o docente</div>

                <div class="flex flex-col sm:flex-row gap-4 sm:items-end mb-6">
                    <div class="relative w-full md:flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#1B396A">
                                <path d="M784-120 532-372q-30 24-69 38t-83 14q-109 0-184.5-75.5T120-580q0-109 75.5-184.5T380-840q109 0 184.5 75.5T640-580q0 44-14 83t-38 69l252 252-56 56ZM380-400q75 0 127.5-52.5T560-580q0-75-52.5-127.5T380-760q-75 0-127.5 52.5T200-580q0 75 52.5 127.5T380-400Z"/>
                            </svg>
                        </div>
                        <input 
                            v-model="search" 
                            type="text" 
                            placeholder="Buscar..." 
                            class="pl-10 w-full h-[45px] rounded-lg border border-gray-300 text-gray-700 focus:border-[var(--institutional-primary)] focus:ring focus:ring-[var(--institutional-primary)] focus:ring-opacity-20 hover:bg-gray-50 transition"
                        />
                    </div>
                    <!-- Filtro por estado -->
                    <div class="w-full md:w-48 flex-shrink-0">
                        <VueSelect 
                            v-model="statusFilter" 
                            :options="statusOptions" 
                            :reduce="option => option.value" 
                            :searchable="false" 
                            :clearable="false" 
                            placeholder="Todos los estados"
                            class="vue-select-custom"
                            @option:selected="onStatusChange"
                        />
                    </div>
                    <!-- Registros por página -->
                    <div class="w-full md:w-44 flex-shrink-0">
                        <VueSelect 
                            v-model="rows" 
                            :options="rowOptions" 
                            :reduce="option => option.value" 
                            :searchable="false" 
                            :clearable="false" 
                            placeholder="Registros"
                            class="vue-select-custom"
                            @option:selected="onRowsChange"
                        />
                    </div>
                </div>
            </div>

            <!-- Listado de Solicitudes Completadas -->
            <div class="bg-white rounded-lg shadow-md border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-bold text-gray-900">Evaluaciones Completadas</h3>
                </div>
                
                <div class="p-4 sm:p-6">
                    <div class="space-y-4">
                        <div v-if="applications.data.length === 0" class="text-center py-8 text-gray-500">
                            No has realizado ninguna evaluación aún.
                        </div>

                        <div v-for="application in applications.data" :key="application.evaluation_id" class="border border-gray-300 rounded-lg p-4 sm:p-5 hover:shadow-md transition-shadow bg-white flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                            
                            <!-- Columna Izquierda: Datos Principales -->
                            <div class="flex-1">
                                <h4 class="text-lg font-semibold text-gray-700 mb-1">{{ application.announcement_name }}</h4>
                                <p class="text-gray-600">
                                    {{ application.teacher_name }}
                                </p>
                            </div>

                            <!-- Columna Central: Metadata -->
                            <div class="flex flex-col gap-2 text-sm text-gray-600 md:px-4 md:border-l md:border-r border-gray-200">
                                 <div class="flex items-center gap-2">
                                    <span>Evaluado el {{ application.evaluation_date }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span>{{ application.documents_count }} Documentos adjuntos</span>
                                </div>
                                <div class="flex items-center gap-2 font-medium">
                                    <span>Puntaje: {{ Math.round(application.score) }} pts</span>
                                </div>
                            </div>

                            <!-- Columna Derecha: Acciones -->
                            <div class="flex flex-col items-end gap-3 min-w-[140px]">
                                <span 
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-white text-xs font-bold shadow-sm border border-gray-100"
                                    :class="{
                                        'text-green-700': application.status === 'approved',
                                        'text-red-700': application.status === 'rejected',
                                        'text-yellow-700': application.status === 'pending',
                                        'text-gray-600': application.status === 'expired',
                                        'text-purple-700': application.status === 'evaluated_by_admin',
                                    }"
                                >
                                    <span 
                                        class="w-2 h-2 rounded-full"
                                        :class="{
                                            'bg-green-500': application.status === 'approved',
                                            'bg-red-500': application.status === 'rejected',
                                            'bg-yellow-500 animate-pulse': application.status === 'pending',
                                            'bg-gray-400': application.status === 'expired',
                                            'bg-purple-500': application.status === 'evaluated_by_admin',
                                        }"
                                    ></span>
                                    {{ getStatusLabel(application.status) }}
                                </span>
                                
                                <Link 
                                    :href="route('evaluator.evaluations.show', application.evaluation_id)" 
                                    class="w-full text-center bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium py-2 px-4 rounded-lg transition-colors shadow-sm flex items-center justify-center gap-2 cursor-pointer"
                                >
                                    Ver Detalles
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Paginación -->
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 rounded-b-lg">
                    <Pagination :links="applications.links" :total="applications.total" :from="applications.from" :to="applications.to" />
                </div>
            </div>
        </div>
    </EvaluatorLayout>
</template>

<style scoped>
:deep(.vue-select-custom .vs__dropdown-toggle) {
    background: linear-gradient(to bottom, #ffffff 0%, #f9fafb 100%);
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    padding: 0.5rem;
    min-height: 42px;
}
:deep(.vue-select-custom .vs__selected) {
    color: #374151;
    font-weight: 500;
}
:deep(.vue-select-custom .vs__search::placeholder) {
    color: #9ca3af;
}
:deep(.vue-select-custom .vs__dropdown-menu) {
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}
:deep(.vue-select-custom .vs__dropdown-option) {
    padding: 0.75rem 1rem;
    color: #374151;
}
:deep(.vue-select-custom .vs__dropdown-option--highlight) {
    background: #1B396A;
    color: white;
}
:deep(.vue-select-custom .vs__open-indicator) {
    fill: #1B396A;
    transform: scale(0.85);
}
:deep(.vue-select-custom .vs__actions) {
    padding-right: 4px;
}
</style>
