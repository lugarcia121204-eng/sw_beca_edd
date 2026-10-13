<script setup>
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import Pagination from '@/Shared/Pagination.vue';
import VueSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';
import { mdiFileDocumentMultiple, mdiAccountPlus, mdiAccountMultiple, mdiEye } from '@mdi/js';
import debounce from 'lodash/debounce';

const props = defineProps({
    applications: Object,
    evaluators: Array, // May be unused now in index, but kept if controller sends it
    filters: Object,
});

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');
const rows = ref(props.filters?.rows || 10);
const sortField = ref(props.filters?.sort_field || '');
const sortDirection = ref(props.filters?.sort_direction || 'asc');

const rowOptions = [
    { label: '5 Registros', value: 5 },
    { label: '10 Registros', value: 10 },
    { label: '25 Registros', value: 25 },
    { label: '50 Registros', value: 50 },
];

const statusOptions = [
    { label: 'Todos los estados', value: '' },
    { label: 'Pendiente', value: 'pending' },
    { label: 'Aprobada', value: 'approved' },
    { label: 'Rechazada', value: 'rejected' },
];

const onStatusChange = () => {
    router.get(route('admin.applications.index'), {
        search: search.value,
        status: status.value,
        rows: rows.value,
        sort_field: sortField.value,
        sort_direction: sortDirection.value,
    }, { preserveState: true, replace: true, preserveScroll: true });
};

const sortBy = (field) => {
    if (sortField.value === field) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortField.value = field;
        sortDirection.value = 'asc';
    }
    router.get(route('admin.applications.index'), {
        search: search.value,
        status: status.value,
        rows: rows.value,
        sort_field: sortField.value,
        sort_direction: sortDirection.value,
    }, { preserveState: true, replace: true });
};

const onRowsChange = () => {
    router.get(route('admin.applications.index'), {
        search: search.value,
        status: status.value,
        rows: rows.value,
        sort_field: sortField.value,
        sort_direction: sortDirection.value,
    }, { preserveState: true, replace: true, preserveScroll: true });
};

const getStatusLabel = (status) => {
    const labels = {
        'pending': 'Pendiente',
        'approved': 'Aprobada',
        'rejected': 'Rechazada',
    };
    return labels[status] || status;
};

const cleanFilters = () => {
    search.value = '';
    status.value = '';
    rows.value = 10;
    sortField.value = '';
    sortDirection.value = 'asc';
    router.get(route('admin.applications.index'), {}, { preserveState: true, replace: true });
};

watch([search, status], debounce(() => {
    router.get(route('admin.applications.index'), {
        search: search.value,
        status: status.value,
        rows: rows.value,
        sort_field: sortField.value,
        sort_direction: sortDirection.value,
    }, { preserveState: true, replace: true });
}, 300));
</script>

<template>
    <Head title="Solicitudes" />

    <AdminLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Solicitudes</h1>
                    <div class="flex items-center gap-2 mt-2 text-sm">
                        <svg viewBox="0 0 24 24" class="w-4 h-4 flex-shrink-0" style="fill: #1B396A;">
                            <path :d="mdiFileDocumentMultiple"/>
                        </svg>
                        <span class="text-gray-900 font-semibold">Solicitudes</span>
                    </div>
                </div>
            </div>

            <!-- Filter Card -->
            <div class="bg-white rounded-lg shadow-md border border-gray-200 p-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-2 gap-3">
                    <div class="flex items-center gap-2">
                         <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#374151">
                            <path d="M440-160q-17 0-28.5-11.5T400-200v-240L168-736q-15-20-4.5-42t36.5-22h560q26 0 36.5 22t-4.5 42L560-440v240q0 17-11.5 28.5T520-160h-80Zm40-308 198-252H282l198 252Zm0 0Z"/>
                        </svg>
                        <h2 class="text-xl font-semibold text-gray-800">Filtro de Búsqueda</h2>
                    </div>
                    <button @click="cleanFilters" class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 flex items-center justify-center gap-2 text-sm font-medium transition cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="currentColor">
                             <path d="M400-240v-80h240v80H400Zm-158 0L15-467l57-57 170 170 366-366 57 57-423 423Zm318-160v-80h240v80H560Zm160-160v-80h240v80H720Z"/>
                        </svg>
                        Limpiar Filtros
                    </button>
                </div>
                <div class="text-sm text-gray-500 mb-4">Buscar y filtrar solicitudes</div>
                
                <div class="flex flex-col md:flex-row gap-4 items-end">
                    <!-- Search Input -->
                    <div class="relative w-full md:flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#1B396A">
                                <path d="M784-120 532-372q-30 24-69 38t-83 14q-109 0-184.5-75.5T120-580q0-109 75.5-184.5T380-840q109 0 184.5 75.5T640-580q0 44-14 83t-38 69l252 252-56 56ZM380-400q75 0 127.5-52.5T560-580q0-75-52.5-127.5T380-760q-75 0-127.5 52.5T200-580q0 75 52.5 127.5T380-400Z"/>
                            </svg>
                        </div>
                        <input 
                            v-model="search" 
                            type="text" 
                            placeholder="Buscar por Docente, Institución..." 
                            class="pl-10 w-full h-[45px] rounded-lg border border-gray-300 text-gray-700 focus:border-[var(--institutional-primary)] focus:ring focus:ring-[var(--institutional-primary)] focus:ring-opacity-20 hover:bg-gray-50 transition" 
                        />
                    </div>
                    
                    <!-- Status Filter -->
                    <div class="w-full md:w-52 flex-shrink-0">
                        <VueSelect
                            v-model="status"
                            :options="statusOptions"
                            :reduce="option => option.value"
                            :searchable="false"
                            :clearable="false"
                            placeholder="Todos los estados"
                            class="vue-select-custom"
                            @option:selected="onStatusChange"
                        />
                    </div>

                    <!-- Rows -->
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

            <!-- Table -->
            <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[980px] text-left">
                        <thead class="bg-[var(--institutional-primary)] text-white uppercase text-xs font-semibold">
                            <tr>
                                <th class="px-3 md:px-6 py-4 whitespace-nowrap">ID</th>
                                <th class="px-3 md:px-6 py-4 whitespace-nowrap">
                                    <div @click="sortBy('user_name')" class="flex items-center gap-1 cursor-pointer hover:text-gray-200 transition">
                                        Docente
                                        <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="currentColor" :class="{ 'opacity-100': sortField === 'user_name', 'opacity-50': sortField !== 'user_name' }">
                                            <path d="M320-440v-287L217-624l-57-56 200-200 200 200-57 56-103-103v287h-80ZM600-80 400-280l57-56 103 103v-287h80v287l103-103 57 56L600-80Z"/>
                                        </svg>
                                    </div>
                                </th>
                                <th class="px-3 md:px-6 py-4 whitespace-nowrap">
                                    <div @click="sortBy('campus')" class="flex items-center gap-1 cursor-pointer hover:text-gray-200 transition">
                                        Institución
                                        <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="currentColor" :class="{ 'opacity-100': sortField === 'campus', 'opacity-50': sortField !== 'campus' }">
                                            <path d="M320-440v-287L217-624l-57-56 200-200 200 200-57 56-103-103v287h-80ZM600-80 400-280l57-56 103 103v-287h80v287l103-103 57 56L600-80Z"/>
                                        </svg>
                                    </div>
                                </th>
                                <th class="px-3 md:px-6 py-4 whitespace-nowrap">
                                    Evaluador(es)
                                </th>
                                <th class="px-3 md:px-6 py-4 whitespace-nowrap">
                                    <div @click="sortBy('status')" class="flex items-center gap-1 cursor-pointer hover:text-gray-200 transition">
                                        Estado
                                        <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="currentColor" :class="{ 'opacity-100': sortField === 'status', 'opacity-50': sortField !== 'status' }">
                                            <path d="M320-440v-287L217-624l-57-56 200-200 200 200-57 56-103-103v287h-80ZM600-80 400-280l57-56 103 103v-287h80v287l103-103 57 56L600-80Z"/>
                                        </svg>
                                    </div>
                                </th>
                                <th class="px-3 md:px-6 py-4 text-center whitespace-nowrap">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="(application, index) in applications.data" :key="application.id" class="hover:bg-gray-50 transition-colors">
                                <td class="px-3 md:px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap">{{ applications.meta.from + index }}</td>
                                <td class="px-3 md:px-6 py-4 text-sm text-gray-900 whitespace-nowrap overflow-hidden">
                                    <div class="font-semibold text-gray-800 truncate max-w-[200px]" :title="application.user?.name">
                                        {{ application.user?.name }}
                                    </div>
                                </td>
                                 <td class="px-3 md:px-6 py-4 text-sm text-gray-600 whitespace-nowrap overflow-hidden">
                                    <div class="truncate max-w-[250px]" :title="application.campus">
                                        {{ application.campus }}
                                    </div>
                                </td>
                                <td class="px-3 md:px-6 py-4 whitespace-nowrap">
                                    <template v-if="application.evaluations && application.evaluations.length > 0">
                                        <span class="inline-flex items-center gap-1 text-sm text-gray-600">
                                            <svg style="width:16px;height:16px; color: #1B396A;" viewBox="0 0 24 24">
                                                <path fill="currentColor" :d="mdiAccountMultiple" />
                                            </svg>
                                            {{ application.evaluations.length }} Evaluadores
                                        </span>
                                    </template>
                                    <template v-else-if="application.status === 'approved' || application.status === 'rejected'">
                                        <span class="text-xs text-gray-400 italic">
                                            Sin evaluadores
                                        </span>
                                    </template>
                                    <Link 
                                        v-else 
                                        :href="route('admin.applications.assign_view', application.id)"
                                        class="inline-flex items-center justify-center gap-2 px-3 md:px-4 py-2 border border-[var(--institutional-primary)] text-[var(--institutional-primary)] rounded-lg hover:bg-[var(--institutional-primary)] hover:text-white transition text-[11px] font-bold uppercase cursor-pointer whitespace-nowrap shadow-sm group"
                                    >
                                        <svg style="width:16px;height:16px" viewBox="0 0 24 24" class="group-hover:text-white transition-colors duration-200">
                                            <path fill="currentColor" :d="mdiAccountPlus" />
                                        </svg>
                                        Asignar
                                    </Link>
                                </td>
                                <td class="px-3 md:px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <span 
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-white text-xs font-bold shadow-sm border border-gray-100"
                                            :class="{
                                                'text-green-700': application.status === 'approved',
                                                'text-red-700': application.status === 'rejected',
                                                'text-yellow-700': application.status === 'pending'
                                            }"
                                        >
                                            <span 
                                                class="w-2 h-2 rounded-full"
                                                :class="{
                                                    'bg-green-500': application.status === 'approved',
                                                    'bg-red-500': application.status === 'rejected',
                                                    'bg-yellow-500': application.status === 'pending'
                                                }"
                                            ></span>
                                            {{ getStatusLabel(application.status) }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-3 md:px-6 py-4 text-center whitespace-nowrap text-xs">
                                    <div class="flex items-center justify-center">
                                        <Link 
                                            :href="route('admin.applications.show', application.id)"
                                            class="inline-flex items-center justify-center gap-2 px-3 md:px-4 py-2 border border-[var(--institutional-primary)] text-[var(--institutional-primary)] rounded-lg hover:bg-[var(--institutional-primary)] hover:text-white transition text-[11px] font-bold uppercase cursor-pointer whitespace-nowrap shadow-sm"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" height="18px" viewBox="0 -960 960 960" width="18px" fill="currentColor">
                                                <path d="M480-320q75 0 127.5-52.5T660-500q0-75-52.5-127.5T480-680q-75 0-127.5 52.5T300-500q0 75 52.5 127.5T480-320Zm0-72q-45 0-76.5-31.5T372-500q0-45 31.5-76.5T480-608q45 0 76.5 31.5T588-500q0 45-31.5 76.5T480-392Zm0 192q-146 0-266-81.5T40-500q54-137 174-218.5T480-800q146 0 266 81.5T920-500q-54 137-174 218.5T480-200Z"/>
                                            </svg>
                                            Ver Detalles
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="applications.data.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    No se encontraron registros
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                 <!-- Pagination and Total -->
                <div class="border-t border-gray-100 bg-gray-50 px-6 py-4" v-if="applications.meta?.links">
                     <Pagination :links="applications.meta.links" :total="applications.meta.total" />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
:deep(.vue-select-custom .vs__dropdown-toggle) {
    background: linear-gradient(to bottom, #ffffff 0%, #f9fafb 100%);
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    padding: 0.5rem;
    min-height: 45px;
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
}

:deep(.vue-select-custom .vs__search) {
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
