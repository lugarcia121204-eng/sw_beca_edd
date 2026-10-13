<script setup>
import { Head, router } from '@inertiajs/vue3';
import SuperAdminLayout from '@/layouts/SuperAdminLayout.vue';
import ApplicationsChart from '@/components/Dashboard/ApplicationsChart.vue';
import { ref, watch, computed } from 'vue';
import { debounce } from 'lodash';
import VueSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';
import { mdiFileDocumentMultiple, mdiCheckCircle, mdiCloseCircle, mdiMagnify, mdiFilterVariant, mdiHome, mdiDownload } from '@mdi/js';

const props = defineProps({
    stats: Object,
    chart: Object,
    filters: Object,
    institutions: Array,
    states: Array,
});

const search = ref(props.filters.search || '');
const institutionId = ref(props.filters.institution_id || null);
const stateId = ref(props.filters.state_id || null);
const statusFilter = ref(props.filters.status || null);

const statusOptions = [
    { label: 'Aprobadas', value: 'approved' },
    { label: 'Rechazadas', value: 'rejected' },
];

const filteredInstitutions = computed(() => {
    if (!stateId.value) return props.institutions;
    return props.institutions.filter(i => i.state_id === stateId.value);
});

const chartData = computed(() => ({
    labels: props.chart.labels,
    datasets: [
        {
            label: 'Aprobados',
            backgroundColor: '#10B981', // Green-500
            data: props.chart.approved,
            borderRadius: 4,
        },
        {
            label: 'Rechazados',
            backgroundColor: '#EF4444', // Red-500
            data: props.chart.rejected,
            borderRadius: 4,
        }
    ]
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom',
            labels: {
                usePointStyle: true,
                padding: 20,
            }
        },
        title: {
            display: false,
        }
    },
    scales: {
        x: {
            grid: {
                display: false
            },
            ticks: {
                 autoSkip: false,
                 maxRotation: 45,
                 minRotation: 0
            }
        },
        y: {
            type: 'linear',
            beginAtZero: true,
            grid: {
                borderDash: [5, 5],
                color: '#f3f4f6'
            },
            ticks: {
                precision: 0
            }
        }
    },
    // Customize bar separation if needed, or stick to defaults
    barPercentage: 0.6,
    categoryPercentage: 0.7
};

// Filter Logic
const updateFilters = debounce(() => {
    router.get(route('superadmin.dashboard'), {
        search: search.value,
        institution_id: institutionId.value,
        state_id: stateId.value,
        status: statusFilter.value,
    }, { preserveState: true, replace: true });
}, 500);

const cleanFilters = () => {
    search.value = '';
    institutionId.value = null;
    stateId.value = null;
    statusFilter.value = null;
    updateFilters();
};

watch([search, institutionId, stateId, statusFilter], () => {
    updateFilters();
});

// When state changes, clear institution if it doesn't belong to the new state
watch(stateId, (newStateId) => {
    if (institutionId.value) {
        const stillValid = props.institutions.some(
            i => i.id === institutionId.value && i.state_id === newStateId
        );
        if (!stillValid) institutionId.value = null;
    }
});

// Export with current filters
const exportData = () => {
    const params = new URLSearchParams();
    if (search.value)        params.append('search', search.value);
    if (institutionId.value) params.append('institution_id', institutionId.value);
    if (stateId.value)       params.append('state_id', stateId.value);
    if (statusFilter.value)  params.append('status', statusFilter.value);
    window.location.href = route('superadmin.dashboard.export') + (params.toString() ? '?' + params.toString() : '');
};


</script>

<template>
    <SuperAdminLayout>
        <Head title="Dashboard" />

        <div class="space-y-8">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Inicio</h1>
                    <div class="flex items-center gap-2 mt-2 text-sm">
                        <svg viewBox="0 0 24 24" class="w-4 h-4 flex-shrink-0" style="fill: #1B396A;">
                            <path :d="mdiHome"/>
                        </svg>
                        <span class="text-gray-900 font-semibold">Inicio</span>
                    </div>
                </div>
                <!-- Export Button -->
                <button
                    @click="exportData"
                    class="w-full sm:w-auto justify-center px-4 py-2.5 bg-[#0D7239] text-white rounded-lg hover:bg-green-800 transition flex items-center gap-2 font-medium cursor-pointer"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="currentColor">
                        <path d="M480-320 280-520l56-58 104 104v-326h80v326l104-104 56 58-200 200ZM240-160q-33 0-56.5-23.5T160-240v-120h80v120h480v-120h80v120q0 33-23.5 56.5T720-160H240Z"/>
                    </svg>
                    Exportar
                </button>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Total -->
                <div class="bg-white rounded-lg shadow-md border-l-4 border-[var(--institutional-primary)] p-4 hover:shadow-lg transition-shadow">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Solicitudes Totales</p>
                            <p class="text-2xl font-bold text-gray-900">{{ stats.total }}</p>
                        </div>
                        <div class="p-2">
                             <svg viewBox="0 0 24 24" class="w-8 h-8 text-[var(--institutional-primary)]" style="fill: currentColor"><path :d="mdiFileDocumentMultiple"/></svg>
                        </div>
                    </div>
                </div>

                <!-- Aprobadas -->
                <div class="bg-white rounded-lg shadow-md border-l-4 border-green-500 p-4 hover:shadow-lg transition-shadow">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Aprobadas</p>
                            <p class="text-2xl font-bold text-gray-900">{{ stats.approved }}</p>
                        </div>
                        <div class="p-2">
                             <svg viewBox="0 0 24 24" class="w-8 h-8 text-green-600" style="fill: currentColor"><path :d="mdiCheckCircle"/></svg>
                        </div>
                    </div>
                </div>

                <!-- Rechazadas -->
                <div class="bg-white rounded-lg shadow-md border-l-4 border-red-500 p-4 hover:shadow-lg transition-shadow">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Rechazadas</p>
                            <p class="text-2xl font-bold text-gray-900">{{ stats.rejected }}</p>
                        </div>
                        <div class="p-2">
                            <svg viewBox="0 0 24 24" class="w-8 h-8 text-red-600" style="fill: currentColor"><path :d="mdiCloseCircle"/></svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="bg-white rounded-lg shadow-md border border-gray-200 p-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
                     <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#374151">
                            <path d="M440-160q-17 0-28.5-11.5T400-200v-240L168-736q-15-20-4.5-42t36.5-22h560q26 0 36.5 22t-4.5 42L560-440v240q0 17-11.5 28.5T520-160h-80Zm40-308 198-252H282l198 252Zm0 0Z"/>
                        </svg>
                        <h2 class="text-xl font-semibold text-gray-800">Filtros de Búsqueda</h2>
                    </div>
                    
                    <button @click="cleanFilters" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 flex items-center gap-2 text-sm font-medium transition cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="currentColor">
                            <path d="M400-240v-80h240v80H400Zm-158 0L15-467l57-57 170 170 366-366 57 57-423 423Zm318-160v-80h240v80H560Zm160-160v-80h240v80H720Z"/>
                        </svg>
                        Limpiar todo
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                    <!-- Search -->
                    <div class="relative w-full">
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Buscar</label>
                        <div class="absolute bottom-0 left-0 pl-3 flex items-center pointer-events-none" style="height:45px">
                            <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#1B396A">
                                <path d="M784-120 532-372q-30 24-69 38t-83 14q-109 0-184.5-75.5T120-580q0-109 75.5-184.5T380-840q109 0 184.5 75.5T640-580q0 44-14 83t-38 69l252 252-56 56ZM380-400q75 0 127.5-52.5T560-580q0-75-52.5-127.5T380-760q-75 0-127.5 52.5T200-580q0 75 52.5 127.5T380-400Z"/>
                            </svg>
                        </div>
                        <input 
                            v-model="search" 
                            type="text" 
                            placeholder="Buscar por ID, nombre..." 
                            class="pl-10 w-full h-[45px] rounded-lg border border-gray-300 text-gray-700 focus:border-[var(--institutional-primary)] focus:ring focus:ring-[var(--institutional-primary)] focus:ring-opacity-20 hover:bg-gray-50 transition text-sm"
                        />
                    </div>

                    <!-- State Filter -->
                    <div class="w-full">
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Filtrar por Estado</label>
                         <VueSelect
                            v-model="stateId"
                            :options="states"
                            :reduce="option => option.id"
                            label="name"
                            :clearable="true"
                            placeholder="Todos los estados"
                            class="vue-select-custom"
                        />
                    </div>

                    <!-- Campus Filter -->
                    <div class="w-full">
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Filtrar por Institución</label>
                        <VueSelect
                            v-model="institutionId"
                            :options="filteredInstitutions"
                            :reduce="option => option.id"
                            label="name"
                            :clearable="true"
                            placeholder="Todos las instituciones"
                            class="vue-select-custom"
                        />
                    </div>

                    <!-- Status Filter -->
                    <div class="w-full">
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Filtrar por Estatus</label>
                        <VueSelect
                            v-model="statusFilter"
                            :options="statusOptions"
                            :reduce="option => option.value"
                            :searchable="false"
                            :clearable="true"
                            placeholder="Todos los estatus"
                            class="vue-select-custom"
                        />
                    </div>
                </div>
            </div>

            <!-- Chart Section -->
            <div class="bg-white rounded-xl shadow-md border border-gray-200 p-6">
                <div class="mb-6">
                    <h2 class="text-xl font-bold text-gray-900">Resultado de Evaluación de Solicitudes por Institución</h2>
                    <p class="text-sm text-gray-500">Distribución de aprobados y rechazados</p>
                </div>
                
                <div class="w-full relative min-h-[256px] md:min-h-[400px]">
                    <ApplicationsChart :chart-data="chartData" :chart-options="chartOptions" />
                </div>
            </div>
        </div>
    </SuperAdminLayout>
</template>

<style scoped>
:deep(.vue-select-custom .vs__dropdown-toggle) {
    background: linear-gradient(to bottom, #ffffff 0%, #f9fafb 100%);
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    padding: 0.5rem;
    height: 45px;
    overflow: hidden;
}
:deep(.vue-select-custom .vs__selected-options) {
    flex-wrap: nowrap !important;
    min-width: 0;
    flex: 1;
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
    color: #111827;
}
:deep(.vue-select-custom .vs__search::placeholder) {
    color: #9ca3af;
}
:deep(.vue-select-custom .vs__dropdown-menu) {
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    z-index: 50;
}
:deep(.vue-select-custom .vs__dropdown-option) {
    padding: 0.75rem 1rem;
    color: #374151;
}
:deep(.vue-select-custom .vs__dropdown-option--highlight) {
    background: #1B396A;
    color: white;
}
:deep(.vue-select-custom .vs__clear),
:deep(.vue-select-custom .vs__open-indicator) {
    fill: #1B396A;
    transition: transform 0.2s;
}
:deep(.vue-select-custom .vs__open-indicator) {
    transform: scale(0.85);
}
:deep(.vue-select-custom .vs__actions) {
    padding-right: 8px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    gap: 4px;
}
</style>

