<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import EvaluatorLayout from '@/layouts/EvaluatorLayout.vue';
import Pagination from '@/Shared/Pagination.vue';
import { ref, watch } from 'vue';
import { debounce } from 'lodash';
import VueSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';
import { 
    mdiFileDocumentMultiple, 
    mdiClockOutline, 
    mdiCheckCircle,
    mdiHome 
} from '@mdi/js';

const props = defineProps({
    stats: Object,
    applications: Object,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const rows = ref(props.filters?.rows || 10);

const rowOptions = [
    { label: '5 Registros', value: 5 },
    { label: '10 Registros', value: 10 },
    { label: '25 Registros', value: 25 },
    { label: '50 Registros', value: 50 },
];

const onSearch = debounce((value) => {
    router.get(route('evaluator.dashboard'), { 
        search: value,
        rows: rows.value
    }, { preserveState: true, replace: true });
}, 500);

const cleanFilters = () => {
    search.value = '';
    rows.value = 10;
    router.get(route('evaluator.dashboard'), {}, { preserveState: true, replace: true });
};

const onRowsChange = () => {
    router.get(route('evaluator.dashboard'), {
        search: search.value,
        rows: rows.value,
    }, { preserveState: true, replace: true, preserveScroll: true });
};

watch(search, (value) => {
    onSearch(value);
});
</script>

<template>
    <EvaluatorLayout>
        <Head title="Inicio" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Dashboard</h1>
                    <div class="flex items-center gap-2 mt-2 text-sm">
                        <svg viewBox="0 0 24 24" class="w-4 h-4 flex-shrink-0" style="fill: #1B396A;">
                            <path :d="mdiHome"/>
                        </svg>
                        <span class="text-gray-900 font-semibold">Inicio</span>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                <!-- Total Asignadas -->
                <div class="bg-white rounded-lg shadow-md border-l-4 border-[var(--institutional-primary)] p-4 hover:shadow-lg transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Total Asignadas</p>
                            <p class="text-2xl font-bold text-gray-900">{{ stats.total }}</p>
                        </div>
                        <div class="p-2">
                             <svg viewBox="0 0 24 24" class="w-8 h-8 text-[var(--institutional-primary)]" style="fill: currentColor"><path :d="mdiFileDocumentMultiple"/></svg>
                        </div>
                    </div>
                </div>

                <!-- Pendientes -->
                <div class="bg-white rounded-lg shadow-md border-l-4 border-[#EAB308] p-4 hover:shadow-lg transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Pendientes</p>
                            <p class="text-2xl font-bold text-gray-900">{{ stats.pendientes }}</p>
                        </div>
                        <div class="p-2">
                             <svg viewBox="0 0 24 24" class="w-8 h-8 text-[#EAB308]" style="fill: currentColor"><path :d="mdiClockOutline"/></svg>
                        </div>
                    </div>
                </div>

                <!-- Evaluadas -->
                <div class="bg-white rounded-lg shadow-md border-l-4 border-[#10B981] p-4 hover:shadow-lg transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Evaluadas</p>
                            <p class="text-2xl font-bold text-gray-900">{{ stats.evaluadas }}</p>
                        </div>
                        <div class="p-2">
                             <svg viewBox="0 0 24 24" class="w-8 h-8 text-[#10B981]" style="fill: currentColor"><path :d="mdiCheckCircle"/></svg>
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
                        <div class="absolute bottom-0 left-0 pl-3 flex items-center pointer-events-none" style="height:45px">
                            <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#1B396A">
                                <path d="M784-120 532-372q-30 24-69 38t-83 14q-109 0-184.5-75.5T120-580q0-109 75.5-184.5T380-840q109 0 184.5 75.5T640-580q0 44-14 83t-38 69l252 252-56 56ZM380-400q75 0 127.5-52.5T560-580q0-75-52.5-127.5T380-760q-75 0-127.5 52.5T200-580q0 75 52.5 127.5T380-400Z"/>
                            </svg>
                        </div>
                        <input 
                            v-model="search" 
                            type="text" 
                            placeholder="Buscar..." 
                            class="pl-10 w-full h-[45px] rounded-lg border border-gray-300 text-gray-700 focus:border-[var(--institutional-primary)] focus:ring focus:ring-[var(--institutional-primary)] focus:ring-opacity-20 hover:bg-gray-50 transition text-sm"
                        />
                    </div>
                    <div class="w-full md:w-52 flex-shrink-0">
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

            <!-- Listado de Solicitudes Pendientes -->
            <div class="bg-white rounded-lg shadow-md border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-bold text-gray-900">Solicitudes Pendientes</h3>
                    <p class="text-sm text-gray-500">Solicitudes que requieren tu evaluación</p>
                </div>
                
                <div class="p-4 sm:p-6">
                    <div class="space-y-4">
                        <div v-if="applications.data.length === 0" class="text-center py-8 text-gray-500 font-medium">
                            No se encontraron registros
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
                                    <svg xmlns="http://www.w3.org/2000/svg" height="18px" viewBox="0 -960 960 960" width="18px" fill="#4B5563">
                                        <path d="M200-80q-33 0-56.5-23.5T120-160v-560q0-33 23.5-56.5T200-800h40v-80h80v80h320v-80h80v80h40q33 0 56.5 23.5T840-720v560q0 33-23.5 56.5T760-80H200Zm0-80h560v-400H200v400Zm0-480h560v-80H200v80Zm0 0v-80 80Z"/>
                                    </svg>
                                    <span>Solicitado el {{ application.application_date }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" height="18px" viewBox="0 -960 960 960" width="18px" fill="#4B5563">
                                        <path d="M320-240h320v-80H320v80Zm0-160h320v-80H320v80ZM240-80q-33 0-56.5-23.5T160-160v-640q0-33 23.5-56.5T240-880h320l240 240v480q0 33-23.5 56.5T720-80H240Zm280-520v-200H240v640h480v-440H520ZM240-800v200-200 640-640Z"/>
                                    </svg>
                                    <span>{{ application.documents_count }} Documentos adjuntos</span>
                                </div>
                            </div>

                            <!-- Columna Derecha: Acciones -->
                            <div class="flex flex-col items-end gap-3 min-w-[140px]">
                                <span 
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-white text-xs font-bold shadow-sm border border-gray-100"
                                    :class="{
                                        'text-yellow-700': application.status === 'Pendiente',
                                        'text-green-700': application.status === 'Evaluada' || application.status === 'Aprobada',
                                        'text-red-700': application.status === 'Rechazada',
                                    }"
                                >
                                    <span 
                                        class="w-2 h-2 rounded-full"
                                        :class="{
                                            'bg-yellow-500 animate-pulse': application.status === 'Pendiente',
                                            'bg-green-500': application.status === 'Evaluada' || application.status === 'Aprobada',
                                            'bg-red-500': application.status === 'Rechazada',
                                        }"
                                    ></span>
                                    {{ application.status }}
                                </span>
                                
                                <Link 
                                    :href="route('evaluator.evaluation.show', application.evaluation_id)" 
                                    class="w-full text-center bg-[var(--institutional-primary)] hover:bg-[var(--institutional-primary-dark)] text-white py-2 px-4 rounded-lg transition shadow-md text-[11px] font-bold uppercase tracking-wider transform hover:-translate-y-0.5 active:translate-y-0"
                                >
                                    Evaluar
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
    padding: 0 4px;
    height: 45px;
    display: flex;
    align-items: center;
}

:deep(.vue-select-custom .vs__selected-options) {
    flex-wrap: nowrap !important;
    min-width: 0;
    flex: 1;
    display: flex;
    align-items: center;
    padding: 0 4px;
}

:deep(.vue-select-custom .vs__selected) {
    color: #374151;
    font-weight: 500;
    max-width: 100%;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    display: block;
    margin: 0;
}

:deep(.vue-select-custom .vs__search) {
    margin: 0;
    padding: 0;
    color: #111827;
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

:deep(.vue-select-custom .vs__actions) {
    padding-right: 4px;
    display: flex;
    align-items: center;
}

:deep(.vue-select-custom .vs__open-indicator) {
    fill: #1B396A;
    transform: scale(0.8);
}
</style>
