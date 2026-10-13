<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import LayoutAuthenticated from '@/layouts/LayoutAuthenticated.vue';
import Pagination from '@/Shared/Pagination.vue';
import { ref, watch, computed } from 'vue';
import { debounce } from 'lodash';
import VueSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';
import { mdiFileDocumentMultiple } from '@mdi/js';

const props = defineProps({
    stats: Object,
    institutions: Object,
    states: Array,
    allInstitutions: Array,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const stateId = ref(props.filters?.state_id || null);
const institutionId = ref(props.filters?.institution_id || null);
const rows = ref(props.filters?.rows || 10);
const sortField = ref(props.filters?.sort_field || 'name');
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
    state_id: stateId.value,
    institution_id: institutionId.value,
    rows: rows.value,
    sort_field: sortField.value,
    sort_direction: sortDirection.value,
});

const onSearch = debounce((value) => {
    router.get(route('superadmin.control-applications'), { ...getFilters(), search: value }, { preserveState: true, replace: true, preserveScroll: true });
}, 500);

const cleanFilters = () => {
    search.value = '';
    stateId.value = null;
    institutionId.value = null;
    rows.value = 10;
    sortField.value = 'name';
    sortDirection.value = 'asc';
    router.get(route('superadmin.control-applications'), {}, { preserveState: true, replace: true });
};

const sortBy = (field) => {
    if (sortField.value === field) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortField.value = field;
        sortDirection.value = 'asc';
    }
    router.get(route('superadmin.control-applications'), getFilters(), { preserveState: true, replace: true, preserveScroll: true });
};

const onRowsChange = () => {
    router.get(route('superadmin.control-applications'), getFilters(), { preserveState: true, replace: true, preserveScroll: true });
};

const onFilterChange = () => {
    router.get(route('superadmin.control-applications'), getFilters(), { preserveState: true, replace: true, preserveScroll: true });
};

const exportData = () => {
    const params = new URLSearchParams();
    if (search.value)        params.append('search', search.value);
    if (institutionId.value) params.append('institution_id', institutionId.value);
    if (stateId.value)       params.append('state_id', stateId.value);
    window.location.href = route('superadmin.control-applications.export') + (params.toString() ? '?' + params.toString() : '');
};

watch(search, (value) => {
    onSearch(value);
});
</script>

<style scoped>
:deep(.vue-select-custom .vs__dropdown-toggle) {
    background: linear-gradient(to bottom, #ffffff 0%, #f9fafb 100%);
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    padding: 0.5rem;
    height: 45px;
    overflow: hidden; /* Prevent content from ever leaving the box */
}
:deep(.vue-select-custom .vs__selected-options) {
    flex-wrap: nowrap !important; /* Force single line */
    min-width: 0; /* Critical for flex truncation */
    flex: 1;
}
:deep(.vue-select-custom .vs__selected) {
    color: #374151;
    font-weight: 500;
    max-width: 100%; /* Fill available space */
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
    flex-shrink: 0; /* Protect icons from shrinking */
    display: flex;
    align-items: center;
    gap: 4px;
}
</style>

<template>
    <LayoutAuthenticated>
        <Head title="Control de Solicitudes - SuperAdmin" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Control de Solicitudes</h1>
                    <div class="flex items-center gap-2 mt-2 text-sm">
                        <svg viewBox="0 0 24 24" class="w-4 h-4 flex-shrink-0" style="fill: #1B396A;">
                            <path :d="mdiFileDocumentMultiple"/>
                        </svg>
                        <span class="text-gray-900 font-semibold">Control de Solicitudes</span>
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

            <!-- Filter Card -->
            <div class="bg-white rounded-lg shadow-md border border-gray-200 p-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-2">
                    <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#374151">
                            <path d="M440-160q-17 0-28.5-11.5T400-200v-240L168-736q-15-20-4.5-42t36.5-22h560q26 0 36.5 22t-4.5 42L560-440v240q0 17-11.5 28.5T520-160h-80Zm40-308 198-252H282l198 252Zm0 0Z"/>
                        </svg>
                        <h2 class="text-xl font-semibold text-gray-800">Filtro de Búsqueda</h2>
                    </div>
                    <button @click="cleanFilters" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 flex items-center gap-2 text-sm font-medium transition cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="currentColor">
                            <path d="M400-240v-80h240v80H400Zm-158 0L15-467l57-57 170 170 366-366 57 57-423 423Zm318-160v-80h240v80H560Zm160-160v-80h240v80H720Z"/>
                        </svg>
                        Limpiar Filtros
                    </button>
                </div>
                <div class="text-sm text-gray-500 mb-4">Buscar por nombre de campus o ID</div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                    <!-- Búsqueda -->
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
                            placeholder="Buscar por ID, nombre, campus..." 
                            class="pl-10 w-full h-[45px] rounded-lg border border-gray-300 text-gray-700 focus:border-[var(--institutional-primary)] focus:ring focus:ring-[var(--institutional-primary)] focus:ring-opacity-20 hover:bg-gray-50 transition"
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

            <!-- Table -->
            <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
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
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="(institution, index) in institutions.data" :key="institution.id" class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 font-medium text-gray-900">
                                    {{ (institutions.meta.current_page - 1) * institutions.meta.per_page + index + 1 }}
                                </td>
                                <td class="px-6 py-4 font-medium text-gray-900">{{ institution.state }}</td>
                                <td class="px-6 py-4 font-semibold text-gray-800">{{ institution.name }}</td>
                                <td class="px-6 py-4 text-center text-sm">
                                    <span class="inline-flex items-center px-4 py-1.5 rounded-full h-10 w-10 bg-white text-[13px] font-bold text-green-700 shadow-sm whitespace-nowrap border border-gray-100">
                                        {{ institution.approved_count }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center text-sm">
                                    <span class="inline-flex items-center px-4 py-1.5 rounded-full h-10 w-10 bg-white text-[13px] font-bold text-red-700 shadow-sm whitespace-nowrap border border-gray-100">
                                        {{ institution.rejected_count }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="!institutions.data || institutions.data.length === 0">
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                    No se encontraron registros
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50" v-if="institutions.meta?.links">
                     <Pagination :links="institutions.meta.links" :total="institutions.meta.total" :from="institutions.meta.from" :to="institutions.meta.to" />
                </div>
            </div>
        </div>
    </LayoutAuthenticated>
</template>
