<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Pagination from '@/Shared/Pagination.vue';
import { ref, watch, computed } from 'vue';
import { debounce } from 'lodash';
import VueSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';
import { 
    mdiFileDocumentMultiple, 
    mdiClockOutline, 
    mdiCheckCircle, 
    mdiCloseCircle,
    mdiHome
} from '@mdi/js';

const props = defineProps({
    stats: Object,
    topInstitutions: Object,
    institutions: Object,
    states: Array,
    allInstitutions: Array,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const rows = ref(props.filters?.rows || 10);
const stateId = ref(props.filters?.state_id || null);
const institutionId = ref(props.filters?.institution_id || null);
const sortField = ref(props.filters?.sort_field || '');
const sortDirection = ref(props.filters?.sort_direction || 'asc');

// Cascading: filter institutions by selected state
const filteredInstitutions = computed(() => {
    if (!stateId.value) return props.allInstitutions || [];
    return (props.allInstitutions || []).filter(i => i.state_id === stateId.value);
});

// When state changes, clear institution if it doesn't belong to new state
watch(stateId, (newStateId) => {
    if (institutionId.value) {
        const stillValid = (props.allInstitutions || []).some(
            i => i.id === institutionId.value && i.state_id === newStateId
        );
        if (!stillValid) institutionId.value = null;
    }
});

const rowOptions = [
    { label: '5 Registros', value: 5 },
    { label: '10 Registros', value: 10 },
    { label: '25 Registros', value: 25 },
    { label: '50 Registros', value: 50 },
];

const getFilters = () => ({
    search: search.value,
    rows: rows.value,
    state_id: stateId.value,
    institution_id: institutionId.value,
    sort_field: sortField.value,
    sort_direction: sortDirection.value,
});

const onSearch = debounce((value) => {
    router.get(route('admin.dashboard'), { ...getFilters(), search: value }, { preserveState: true, replace: true, preserveScroll: true });
}, 500);

const cleanFilters = () => {
    search.value = '';
    rows.value = 10;
    stateId.value = null;
    institutionId.value = null;
    sortField.value = '';
    sortDirection.value = 'asc';
    router.get(route('admin.dashboard'), {}, { preserveState: true, replace: true });
};

const sortBy = (field) => {
    if (sortField.value === field) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortField.value = field;
        sortDirection.value = 'asc';
    }
    router.get(route('admin.dashboard'), getFilters(), { preserveState: true, replace: true });
};

const onRowsChange = () => {
    router.get(route('admin.dashboard'), getFilters(), { preserveState: true, replace: true, preserveScroll: true });
};

const onFilterChange = () => {
    router.get(route('admin.dashboard'), getFilters(), { preserveState: true, replace: true, preserveScroll: true });
};

const exportData = () => {
    const params = new URLSearchParams();
    if (search.value)        params.append('search', search.value);
    if (institutionId.value) params.append('institution_id', institutionId.value);
    if (stateId.value)       params.append('state_id', stateId.value);
    window.location.href = route('admin.dashboard.export') + (params.toString() ? '?' + params.toString() : '');
};

watch(search, (value) => {
    onSearch(value);
});

const getMaxCount = () => {
    if (!props.topInstitutions?.data || props.topInstitutions.data.length === 0) return 1;
    return Math.max(...props.topInstitutions.data.map(i => i.applications_count));
};

const getProgressWidth = (count) => {
    const max = getMaxCount();
    return (count / max) * 100;
};
</script>

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

<template>
    <AdminLayout>
        <Head title="Dashboard Admin" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Inicio</h1>
                    <div class="flex items-center gap-2 mt-2 text-sm">
                        <svg viewBox="0 0 24 24" class="w-4 h-4 flex-shrink-0" style="fill: #1B396A;">
                            <path :d="mdiHome"/>
                        </svg>
                        <span class="text-gray-900 font-semibold">Inicio</span>
                    </div>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Total Solicitudes -->
                <div class="bg-white rounded-lg shadow-md border-l-4 border-[var(--institutional-primary)] p-4 hover:shadow-lg transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Total Solicitudes</p>
                            <p class="text-2xl font-bold text-gray-900">{{ stats.total }}</p>
                        </div>
                        <div class="p-2">
                             <svg viewBox="0 0 24 24" class="w-8 h-8 text-[var(--institutional-primary)]" style="fill: currentColor">
                                <path :d="mdiFileDocumentMultiple"/>
                             </svg>
                        </div>
                    </div>
                </div>

                <!-- Pendientes -->
                <div class="bg-white rounded-lg shadow-md border-l-4 border-[#EAB308] p-4 hover:shadow-lg transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Pendientes</p>
                            <p class="text-2xl font-bold text-[#ca8a04]">{{ stats.pending }}</p>
                        </div>
                        <div class="p-2">
                             <svg viewBox="0 0 24 24" class="w-8 h-8 text-[#ca8a04]" style="fill: currentColor">
                                <path :d="mdiClockOutline"/>
                             </svg>
                        </div>
                    </div>
                </div>

                <!-- Aprobadas -->
                <div class="bg-white rounded-lg shadow-md border-l-4 border-[#10B981] p-4 hover:shadow-lg transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Aprobadas</p>
                            <p class="text-2xl font-bold text-[#16a34a]">{{ stats.approved }}</p>
                        </div>
                        <div class="p-2">
                             <svg viewBox="0 0 24 24" class="w-8 h-8 text-[#16a34a]" style="fill: currentColor">
                                <path :d="mdiCheckCircle"/>
                             </svg>
                        </div>
                    </div>
                </div>

                <!-- Rechazadas -->
                <div class="bg-white rounded-lg shadow-md border-l-4 border-[#EF4444] p-4 hover:shadow-lg transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Rechazadas</p>
                            <p class="text-2xl font-bold text-[#dc2626]">{{ stats.rejected }}</p>
                        </div>
                        <div class="p-2">
                             <svg viewBox="0 0 24 24" class="w-8 h-8 text-[#dc2626]" style="fill: currentColor">
                                <path :d="mdiCloseCircle"/>
                             </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Solicitudes por Campus -->
            <div class="bg-white rounded-lg shadow-md border border-gray-200 p-4 sm:p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Solicitudes por Institución</h2>
                
                <div class="space-y-4">
                    <div v-for="institution in topInstitutions.data" :key="institution.id" class="flex items-center gap-4">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-700 mb-2">{{ institution.name }}</p>
                            <div class="w-full bg-gray-200 rounded-full h-6 overflow-hidden">
                                <div 
                                    class="h-full bg-gradient-to-r from-[var(--institutional-primary)] to-[#2563eb] rounded-full transition-all duration-300 flex items-center justify-end pr-2"
                                    :style="{ width: getProgressWidth(institution.applications_count) + '%' }"
                                ></div>
                            </div>
                            <div class="flex justify-between text-xs text-gray-500 mt-1">
                                <span>{{ institution.state?.name || 'N/A' }}</span> <!-- estado -> state, nombre -> name -->
                            </div>
                        </div>
                        <div class="flex items-center justify-center bg-white rounded-full h-10 w-10 shadow-sm border border-gray-100 flex-shrink-0">
                            <p class="text-lg font-bold text-[var(--institutional-primary)]">{{ institution.applications_count }}</p>
                        </div>
                    </div>

                    <div v-if="!topInstitutions.data || topInstitutions.data.length === 0" class="text-center py-8 text-gray-500">
                        No hay datos disponibles
                    </div>
                </div>

                <!-- Pagination for Top Institutions -->
                <div v-if="topInstitutions.total > 0" class="mt-6 pt-4 border-t border-gray-200">
                    <Pagination 
                        :links="topInstitutions.links" 
                        :total="topInstitutions.total" 
                        :from="topInstitutions.from" 
                        :to="topInstitutions.to" 
                    />
                </div>
            </div>

            <!-- Filter Section -->
            <div class="bg-white rounded-lg shadow-md border border-gray-200 p-4">
                <div class="flex flex-col md:flex-row items-center justify-between mb-4 gap-4">
                    <div class="flex items-center gap-2 w-full md:w-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#374151">
                            <path d="M440-160q-17 0-28.5-11.5T400-200v-240L168-736q-15-20-4.5-42t36.5-22h560q26 0 36.5 22t-4.5 42L560-440v240q0 17-11.5 28.5T520-160h-80Zm40-308 198-252H282l198 252Zm0 0Z"/>
                        </svg>
                        <h2 class="text-xl font-semibold text-gray-800">Filtro de Búsqueda</h2>
                    </div>
                    <div class="flex flex-col md:flex-row items-center gap-3 w-full md:w-auto">
                        <button @click="cleanFilters" class="w-full md:w-auto justify-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 flex items-center gap-2 text-sm font-medium transition cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="currentColor">
                                <path d="M400-240v-80h240v80H400Zm-158 0L15-467l57-57 170 170 366-366 57 57-423 423Zm318-160v-80h240v80H560Zm160-160v-80h240v80H720Z"/>
                            </svg>
                            Limpiar Filtros
                        </button>
                        <button @click="exportData" class="w-full md:w-auto justify-center px-4 py-2 bg-[#0D7239] text-white rounded-lg hover:bg-green-800 flex items-center gap-2 text-sm font-medium transition cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="currentColor">
                                <path d="M480-320 280-520l56-58 104 104v-326h80v326l104-104 56 58-200 200ZM240-160q-33 0-56.5-23.5T160-240v-120h80v120h480v-120h80v120q0 33-23.5 56.5T720-160H240Z"/>
                            </svg>
                            Exportar
                        </button>
                    </div>
                </div>
                
                <div class="text-sm text-gray-500 mb-4">Buscar por nombre de institución o ID</div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 items-end">
                    <div class="relative">
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Buscar</label>
                        <div class="absolute bottom-0 left-0 pl-3 flex items-center pointer-events-none" style="height:45px">
                            <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#1B396A">
                                <path d="M784-120 532-372q-30 24-69 38t-83 14q-109 0-184.5-75.5T120-580q0-109 75.5-184.5T380-840q109 0 184.5 75.5T640-580q0 44-14 83t-38 69l252 252-56 56ZM380-400q75 0 127.5-52.5T560-580q0-75-52.5-127.5T380-760q-75 0-127.5 52.5T200-580q0 75 52.5 127.5T380-400Z"/>
                            </svg>
                        </div>
                        <input 
                            v-model="search" 
                            type="text" 
                            placeholder="Buscar por ID, nombre, institución..." 
                            class="pl-10 w-full h-[45px] rounded-lg border border-gray-300 text-gray-700 focus:border-[var(--institutional-primary)] focus:ring focus:ring-[var(--institutional-primary)] focus:ring-opacity-20 hover:bg-gray-50 transition text-sm"
                        />
                    </div>
                    <!-- Estado -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Filtrar por Estado</label>
                        <VueSelect
                            v-model="stateId"
                            :options="states"
                            :reduce="option => option.id"
                            label="name"
                            :clearable="true"
                            placeholder="Todos los estados"
                            class="vue-select-custom"
                            @option:selected="onFilterChange"
                            @option:deselected="onFilterChange"
                        />
                    </div>
                    <!-- Institución -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Filtrar por Institución</label>
                        <VueSelect
                            v-model="institutionId"
                            :options="filteredInstitutions"
                            :reduce="option => option.id"
                            label="name"
                            :clearable="true"
                            placeholder="Todas las instituciones"
                            class="vue-select-custom"
                            @option:selected="onFilterChange"
                            @option:deselected="onFilterChange"
                        />
                    </div>
                    <!-- Registros -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Registros</label>
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

            <!-- Table Section -->
            <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[920px] text-sm text-left">
                        <thead class="bg-[var(--institutional-primary)] text-white uppercase text-xs font-semibold">
                            <tr>
                                <th scope="col" class="px-6 py-4 tracking-wider">ID</th>
                                <th scope="col" class="px-6 py-4 tracking-wider">
                                    <div @click="sortBy('state')" class="flex items-center gap-1 cursor-pointer hover:text-gray-200 transition">
                                        Estado
                                        <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="currentColor" :class="{ 'opacity-100': sortField === 'state', 'opacity-50': sortField !== 'state' }">
                                            <path d="M320-440v-287L217-624l-57-56 200-200 200 200-57 56-103-103v287h-80ZM600-80 400-280l57-56 103 103v-287h80v287l103-103 57 56L600-80Z"/>
                                        </svg>
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-4 tracking-wider">
                                    <div @click="sortBy('name')" class="flex items-center gap-1 cursor-pointer hover:text-gray-200 transition">
                                        Institución
                                        <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="currentColor" :class="{ 'opacity-100': sortField === 'name', 'opacity-50': sortField !== 'name' }">
                                            <path d="M320-440v-287L217-624l-57-56 200-200 200 200-57 56-103-103v287h-80ZM600-80 400-280l57-56 103 103v-287h80v287l103-103 57 56L600-80Z"/>
                                        </svg>
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-4 text-center tracking-wider">Aprobadas</th>
                                <th scope="col" class="px-6 py-4 text-center tracking-wider">Rechazadas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-for="(institution, index) in institutions.data" :key="institution.id" class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 font-medium text-gray-900">
                                    {{ institutions.meta.from + index }}
                                </td>
                                <td class="px-6 py-4 font-medium text-gray-900">{{ institution.state }}</td>
                                <td class="px-6 py-4 text-gray-800 font-semibold">{{ institution.name }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="w-8 h-8 inline-flex items-center justify-center text-[13px] font-bold text-green-700 bg-white rounded-full shadow-sm border border-gray-100">
                                        {{ institution.approved_count }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="w-8 h-8 inline-flex items-center justify-center text-[13px] font-bold text-red-700 bg-white rounded-full shadow-sm border border-gray-100">
                                        {{ institution.rejected_count }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="!institutions.data || institutions.data.length === 0">
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                    No se encontraron registros.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination for Table -->
                <div v-if="institutions.meta" class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    <Pagination 
                        :links="institutions.meta.links" 
                        :total="institutions.meta.total" 
                        :from="institutions.meta.from" 
                        :to="institutions.meta.to" 
                    />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
