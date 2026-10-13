<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import TeacherLayout from '@/layouts/TeacherLayout.vue';
import Pagination from '@/Shared/Pagination.vue';
import { mdiHome } from '@mdi/js';
import { ref, watch, onMounted } from 'vue';
import { debounce } from 'lodash';
import { alertaExito } from '@/utils/alerts';
import VueSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';

const props = defineProps({
    applications: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const rows = ref(props.filters.rows || 10);
const sortField = ref(props.applications.meta?.sort_field || 'created_at');
const sortDirection = ref(props.applications.meta?.sort_direction || 'desc');

const rowOptions = [
    { label: '5 Registros', value: 5 },
    { label: '10 Registros', value: 10 },
    { label: '25 Registros', value: 25 },
    { label: '50 Registros', value: 50 },
];

const onRowsChange = () => {
    router.get(route('teacher.dashboard'), {
        search: search.value,
        rows: rows.value,
        sort_field: sortField.value,
        sort_direction: sortDirection.value
    }, { preserveState: true, replace: true });
};

const sortBy = (field) => {
    if (sortField.value === field) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortField.value = field;
        sortDirection.value = 'asc';
    }
    router.get(route('teacher.dashboard'), { 
        search: search.value,
        rows: rows.value,
        sort_field: sortField.value,
        sort_direction: sortDirection.value
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const cleanFilters = () => {
    search.value = '';
    rows.value = 10;
    sortField.value = 'created_at';
    sortDirection.value = 'desc';
    router.get(route('teacher.dashboard'), {}, { preserveState: true, replace: true });
};

const onSearch = debounce((value) => {
    router.get(route('teacher.dashboard'), { 
        search: value,
        rows: rows.value,
        sort_field: sortField.value,
        sort_direction: sortDirection.value
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}, 500);

watch(search, (value) => {
    onSearch(value);
});

onMounted(() => {
    const page = usePage();
    if (page.props.flash?.success) {
        alertaExito('¡Éxito!', page.props.flash.success);
    }
});
</script>

<template>
    <TeacherLayout>
        <Head title="Inicio" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Mis Solicitudes</h1>
                    <div class="flex items-center gap-2 mt-2 text-sm">
                        <svg viewBox="0 0 24 24" class="w-4 h-4 flex-shrink-0" style="fill: #1B396A;">
                            <path :d="mdiHome"/>
                        </svg>
                        <span class="text-gray-900 font-semibold">Inicio</span>
                    </div>
                </div>
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
                <div class="text-sm text-gray-500 mb-4">Buscar solicitudes por ID o nombre de convocatoria</div>
                <div class="flex flex-col sm:flex-row gap-4 sm:items-end">
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
                    <table class="w-full text-sm text-left">
                        <thead class="bg-[var(--institutional-primary)] text-white uppercase text-xs font-semibold">
                            <tr>
                                <th scope="col" class="px-6 py-4 tracking-wider">ID</th>
                                <th scope="col" class="px-6 py-4 tracking-wider">
                                    <div @click="sortBy('announcement')" class="flex items-center gap-1 cursor-pointer hover:text-gray-200 transition">
                                        Convocatoria
                                        <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="currentColor" :class="{ 'opacity-100': sortField === 'announcement', 'opacity-50': sortField !== 'announcement' }">
                                            <path d="M320-440v-287L217-624l-57-56 200-200 200 200-57 56-103-103v287h-80ZM600-80 400-280l57-56 103 103v-287h80v287l103-103 57 56L600-80Z"/>
                                        </svg>
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-4 tracking-wider">
                                    <div @click="sortBy('created_at')" class="flex items-center gap-1 cursor-pointer hover:text-gray-200 transition">
                                        Fecha de Postulación
                                        <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="currentColor" :class="{ 'opacity-100': sortField === 'created_at', 'opacity-50': sortField !== 'created_at' }">
                                            <path d="M320-440v-287L217-624l-57-56 200-200 200 200-57 56-103-103v287h-80ZM600-80 400-280l57-56 103 103v-287h80v287l103-103 57 56L600-80Z"/>
                                        </svg>
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-4 text-center tracking-wider">
                                    <div @click="sortBy('status')" class="flex items-center justify-center gap-1 cursor-pointer hover:text-gray-200 transition">
                                        Estado
                                        <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="currentColor" :class="{ 'opacity-100': sortField === 'status', 'opacity-50': sortField !== 'status' }">
                                            <path d="M320-440v-287L217-624l-57-56 200-200 200 200-57 56-103-103v287h-80ZM600-80 400-280l57-56 103 103v-287h80v287l103-103 57 56L600-80Z"/>
                                        </svg>
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-4 text-center tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="(application, index) in applications.data" :key="application.id" class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 font-medium text-gray-900">
                                    {{ (applications.meta?.from || 1) + index }}
                                </td>
                                <td class="px-6 py-4 font-medium text-gray-900">{{ application.announcement?.name }}</td>
                                <td class="px-6 py-4 text-gray-600">
                                    {{ new Date(application.created_at).toLocaleDateString('es-ES') }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center">
                                        <span v-if="application.status === 'approved'"
                                            class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-md bg-white text-[13px] font-bold text-green-700 shadow-sm whitespace-nowrap">
                                            <span class="w-2.5 h-2.5 rounded-full bg-green-500 animate-pulse"></span>
                                            Aceptada
                                        </span>
                                        <span v-else-if="application.status === 'rejected'"
                                            class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-md bg-white text-[13px] font-bold text-gray-700 shadow-sm whitespace-nowrap">
                                            <span class="w-2.5 h-2.5 rounded-full bg-gray-500"></span>
                                            No Aprobada
                                        </span>
                                        <span v-else-if="application.status === 'pending'"
                                            class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-md bg-white text-[13px] font-bold text-yellow-700 shadow-sm whitespace-nowrap">
                                            <span class="w-2.5 h-2.5 rounded-full bg-yellow-500"></span>
                                            Pendiente
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <Link :href="route('teacher.applications.show', application.id)" 
                                       class="inline-flex items-center justify-center gap-2 px-4 py-2 border border-[var(--institutional-primary)] text-[var(--institutional-primary)] rounded-lg hover:bg-[var(--institutional-primary)] hover:text-white transition text-xs font-bold uppercase cursor-pointer whitespace-nowrap shadow-sm font-bold">
                                         <svg xmlns="http://www.w3.org/2000/svg" height="18px" viewBox="0 -960 960 960" width="18px" fill="currentColor">
                                            <path d="M480-320q75 0 127.5-52.5T660-500q0-75-52.5-127.5T480-680q-75 0-127.5 52.5T300-500q0 75 52.5 127.5T480-320Zm0-72q-45 0-76.5-31.5T372-500q0-45 31.5-76.5T480-608q45 0 76.5 31.5T588-500q0 45-31.5 76.5T480-392Zm0 192q-146 0-266-81.5T40-500q54-137 174-218.5T480-800q146 0 266 81.5T920-500q-54 137-174 218.5T480-200Z"/>
                                        </svg>
                                        Ver Detalles
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="applications.data.length === 0">
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                    No has realizado ninguna solicitud de beca aún.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                 <!-- Pagination -->
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50" v-if="applications.meta">
                     <Pagination :links="applications.meta.links" :total="applications.meta.total" :from="applications.meta.from" :to="applications.meta.to" />
                </div>
            </div>
        </div>
    </TeacherLayout>
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

