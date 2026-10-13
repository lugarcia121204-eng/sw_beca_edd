<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import LayoutAuthenticated from '@/layouts/LayoutAuthenticated.vue';
import Pagination from '@/Shared/Pagination.vue';
import { ref, watch } from 'vue';
import { debounce } from 'lodash';
import { useCan } from '@/composables/usePermissions';
import VueSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';
import { mdiBullhorn } from '@mdi/js';
import { alertaPregunta, alertaExito, alertaError } from '@/utils/alerts.js';

const props = defineProps({
    announcements: {
        type: Object,
        required: true,
    },
    title: {
        type: String,
        required: true,
    },
    routeName: {
        type: String,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
    },
    canCreate: {
        type: Boolean,
        default: true
    },
    restrictionMessage: {
        type: String,
        default: ''
    }
});

const search = ref(props.filters.search);
const currentFile = ref(null);
const rows = ref(props.filters.rows || 10);
const sortField = ref(props.filters.order || 'id');
const sortDirection = ref(props.filters.direction || 'asc');
const expandedRows = ref({});
const showRestrictionAlert = ref(false);
let restrictionTimer = null;

const triggerRestrictionAlert = () => {
    showRestrictionAlert.value = true;
    if (restrictionTimer) clearTimeout(restrictionTimer);
    restrictionTimer = setTimeout(() => {
        showRestrictionAlert.value = false;
    }, 5000);
};

const rowOptions = [
    { label: '5 Registros', value: 5 },
    { label: '10 Registros', value: 10 },
    { label: '25 Registros', value: 25 },
    { label: '50 Registros', value: 50 },
];

const onSearch = debounce((value) => {
    router.get(route(`${props.routeName}index`), {
        search: value,
        rows: rows.value,
        order: sortField.value,
        direction: sortDirection.value
    }, { preserveState: true, replace: true });
}, 500);

const onRowsChange = () => {
    router.get(route(`${props.routeName}index`), {
        search: search.value,
        rows: rows.value,
        order: sortField.value,
        direction: sortDirection.value
    }, { preserveState: true, replace: true });
};

watch(search, (value) => {
    onSearch(value);
});

const cleanFilters = () => {
    search.value = '';
    rows.value = 10;
    sortField.value = 'id';
    sortDirection.value = 'asc';
    router.get(route(`${props.routeName}index`), {}, { preserveState: true, replace: true });
};

const sortBy = (field) => {
    if (sortField.value === field) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortField.value = field;
        sortDirection.value = 'asc';
    }
    router.get(route(`${props.routeName}index`), {
        search: search.value,
        rows: rows.value,
        order: sortField.value,
        direction: sortDirection.value
    }, { preserveState: true, replace: true });
};

const deleteAnnouncement = async (id) => {
    const confirmed = await alertaPregunta(
        '¿Eliminar convocatoria?',
        'Esta acción no se puede deshacer'
    );
    if (confirmed) {
        router.delete(route(`${props.routeName}destroy`, { announcement: id }), {
            onSuccess: () => alertaExito('¡Eliminado!', 'Convocatoria eliminada correctamente'),
            onError: () => alertaError('Error', 'No se pudo eliminar la convocatoria')
        });
    }
};

const getStatusBadge = (status) => {
    const badges = {
        activa: 'bg-green-100 text-green-800',
        cerrada: 'bg-red-100 text-red-800',
        pendiente: 'bg-yellow-100 text-yellow-800'
    };
    return badges[status] || 'bg-gray-100 text-gray-800';
};

const viewFile = (announcement) => {
    if (expandedRows.value[announcement.id]) {
        expandedRows.value[announcement.id] = false;
        currentFile.value = null;
    } else {
        if (announcement.file_path) {
            currentFile.value = {
                url: route('announcements.preview', announcement.id),
                name: announcement.file_name,
                type: announcement.file_type,
                announcement: announcement.name,
                id: announcement.id
            };
        } else if (announcement.image_path) {
            currentFile.value = {
                url: route('announcements.preview', { announcement: announcement.id, type: 'image' }),
                name: 'Imagen de Convocatoria',
                type: 'image/png', // Generic image type
                announcement: announcement.name,
                id: announcement.id
            };
        }
        expandedRows.value[announcement.id] = true;
    }
};

const closeViewer = (id) => {
    expandedRows.value[id] = false;
    if (Object.values(expandedRows.value).every(v => v === false)) {
        currentFile.value = null;
    }
};
</script>

<template>
    <LayoutAuthenticated>

        <Head :title="title" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ title }}</h1>
                    <div class="flex items-center gap-2 mt-2 text-sm">
                        <svg viewBox="0 0 24 24" class="w-4 h-4 flex-shrink-0" style="fill: #1B396A;">
                            <path :d="mdiBullhorn" />
                        </svg>
                        <span class="text-gray-900 font-semibold">Convocatorias</span>
                    </div>
                </div>
                <div class="flex items-center gap-2 group relative">
                    <Link v-if="useCan('announcements.create') && canCreate" :href="route(`${routeName}create`)"
                        class="w-full sm:w-auto justify-center px-4 py-2.5 bg-[var(--institutional-primary)] text-white rounded-lg hover:bg-[#0f2347] transition flex items-center gap-2 font-medium">
                        <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px"
                            fill="currentColor">
                            <path d="M440-440H200v-80h240v-240h80v240h240v80H520v240h-80v-240Z" />
                        </svg>
                        Agregar
                    </Link>
                    <button v-else-if="useCan('announcements.create')"
                        @click="triggerRestrictionAlert"
                        class="w-full sm:w-auto justify-center px-4 py-2.5 bg-gray-300 text-gray-500 rounded-lg cursor-not-allowed flex items-center gap-2 font-medium">
                        <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px"
                            fill="currentColor">
                            <path d="M440-440H200v-80h240v-240h80v240h240v80H520v240h-80v-240Z" />
                        </svg>
                        Agregar
                    </button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Convocatorias Activas -->
                <div
                    class="bg-white rounded-lg shadow-md border-l-4 border-green-500 p-4 hover:shadow-lg transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Convocatorias
                                Activas</p>
                            <p class="text-2xl font-bold text-gray-900">{{announcements.data.filter(c => c.status ===
                                'activa').length }}</p>
                        </div>
                        <div class="p-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Convocatorias Finalizadas -->
                <div
                    class="bg-white rounded-lg shadow-md border-l-4 border-red-500 p-4 hover:shadow-lg transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Convocatorias
                                Finalizadas</p>
                            <p class="text-2xl font-bold text-gray-800">{{announcements.data.filter(c => c.status ===
                                'cerrada').length }}</p>
                        </div>
                        <div class="p-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-red-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Aviso temporal: Solo una convocatoria activa/pendiente a la vez -->
            <transition
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="transform -translate-y-4 opacity-0 scale-95"
                enter-to-class="transform translate-y-0 opacity-100 scale-100"
                leave-active-class="transition duration-200 ease-in"
                leave-from-class="opacity-100 scale-100"
                leave-to-class="opacity-0 scale-95"
            >
                <div v-if="showRestrictionAlert"
                    class="relative flex items-center gap-4 px-5 py-4 rounded-lg bg-white shadow-sm border border-gray-100"
                    style="border-left: 5px solid #D97706;"
                >
                    <div class="flex-shrink-0" style="color: #D97706;">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-[10px] uppercase font-bold tracking-widest mb-0.5" style="color: #D97706; opacity: 0.8;">Aviso</span>
                        <span class="text-sm font-bold leading-tight text-gray-800">
                            Solo puede existir una convocatoria <strong style="color: #D97706;">Activa o Pendiente</strong> a la vez — cuando llegue la fecha de publicación de una nueva, el sistema <strong style="color: #D97706;">cerrará</strong> la actual automáticamente.
                        </span>
                    </div>
                    <button type="button" @click="showRestrictionAlert = false" class="ml-auto p-2 text-gray-300 hover:opacity-60 transition-all cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </transition>

            <!-- Filter Card -->
            <div class="bg-white rounded-lg shadow-md border border-gray-200 p-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-2">
                    <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px"
                            fill="#374151">
                            <path
                                d="M440-160q-17 0-28.5-11.5T400-200v-240L168-736q-15-20-4.5-42t36.5-22h560q26 0 36.5 22t-4.5 42L560-440v240q0 17-11.5 28.5T520-160h-80Zm40-308 198-252H282l198 252Zm0 0Z" />
                        </svg>
                        <h2 class="text-xl font-semibold text-gray-800">Filtro de Búsqueda</h2>
                    </div>
                    <button @click="cleanFilters"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 flex items-center gap-2 text-sm font-medium transition cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px"
                            fill="currentColor">
                            <path
                                d="M400-240v-80h240v80H400Zm-158 0L15-467l57-57 170 170 366-366 57 57-423 423Zm318-160v-80h240v80H560Zm160-160v-80h240v80H720Z" />
                        </svg>
                        Limpiar Filtros
                    </button>
                </div>
                <div class="text-sm text-gray-500 mb-4">Buscar y filtrar convocatorias</div>
                <div class="flex flex-col sm:flex-row gap-4 sm:items-end">
                    <div class="relative w-full md:flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px"
                                fill="#1B396A">
                                <path
                                    d="M784-120 532-372q-30 24-69 38t-83 14q-109 0-184.5-75.5T120-580q0-109 75.5-184.5T380-840q109 0 184.5 75.5T640-580q0 44-14 83t-38 69l252 252-56 56ZM380-400q75 0 127.5-52.5T560-580q0-75-52.5-127.5T380-760q-75 0-127.5 52.5T200-580q0 75 52.5 127.5T380-400Z" />
                            </svg>
                        </div>
                        <input v-model="search" type="text" placeholder="Buscar..."
                            class="pl-10 w-full h-[45px] rounded-lg border border-gray-300 text-gray-700 focus:border-[var(--institutional-primary)] focus:ring focus:ring-[var(--institutional-primary)] focus:ring-opacity-20 hover:bg-gray-50 transition" />
                    </div>
                    <div class="w-full md:w-52 flex-shrink-0">
                        <VueSelect v-model="rows" :options="rowOptions" :reduce="option => option.value"
                            :searchable="false" :clearable="false" placeholder="Registros" class="vue-select-custom"
                            @option:selected="onRowsChange" />
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[980px] text-sm text-left">
                        <thead class="bg-[var(--institutional-primary)] text-white uppercase text-xs font-semibold">
                            <tr>
                                <th scope="col" class="px-6 py-4 text-center tracking-wider" style="width: 80px;">
                                    ID
                                </th>
                                <th scope="col" class="px-6 py-4 text-left tracking-wider">
                                    <div @click="sortBy('name')"
                                        class="flex items-center gap-1 cursor-pointer hover:text-gray-200 transition">
                                        Nombre
                                        <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="currentColor" :class="{ 'opacity-100': sortField === 'name', 'opacity-50': sortField !== 'name' }">
                                            <path d="M320-440v-287L217-624l-57-56 200-200 200 200-57 56-103-103v287h-80ZM600-80 400-280l57-56 103 103v-287h80v287l103-103 57 56L600-80Z"/>
                                        </svg>
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-4 text-center tracking-wider" style="width: 150px;">
                                    <div @click="sortBy('status')"
                                        class="flex items-center justify-center gap-1 cursor-pointer hover:text-gray-200 transition w-full">
                                        Estado
                                        <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="currentColor" :class="{ 'opacity-100': sortField === 'status', 'opacity-50': sortField !== 'status' }">
                                            <path d="M320-440v-287L217-624l-57-56 200-200 200 200-57 56-103-103v287h-80ZM600-80 400-280l57-56 103 103v-287h80v287l103-103 57 56L600-80Z"/>
                                        </svg>
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-4 text-center tracking-wider" style="width: 120px;">Archivo</th>
                                <th scope="col" class="px-6 py-4 text-center tracking-wider" style="width: 150px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <template v-for="(announcement, index) in announcements.data" :key="announcement.id">
                                <tr 
                                    class="hover:bg-gray-50 transition"
                                    :class="{'bg-blue-50': expandedRows[announcement.id]}"
                                >
                                    <td class="px-6 py-4 text-center text-gray-900 font-medium">{{ announcements.meta.from + index }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <svg viewBox="0 0 24 24" class="w-5 h-5 flex-shrink-0" style="fill: #1B396A;">
                                                <path :d="mdiBullhorn" />
                                            </svg>
                                            <span class="text-gray-900 font-medium">{{ announcement.name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center">
                                            <span v-if="announcement.status === 'activa'"
                                                class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-md bg-white text-[13px] font-bold text-green-700 shadow-sm">
                                                <span class="w-2.5 h-2.5 rounded-full bg-green-500 animate-pulse"></span>
                                                Activa
                                            </span>
                                            <span v-else-if="announcement.status === 'cerrada'"
                                                class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-md bg-white text-[13px] font-bold text-red-700 shadow-sm">
                                                <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>
                                                Cerrada
                                            </span>
                                            <span v-else-if="announcement.status === 'pendiente'"
                                                class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-md bg-white text-[13px] font-bold text-yellow-700 shadow-sm">
                                                <span class="w-2.5 h-2.5 rounded-full bg-yellow-500"></span>
                                                Pendiente
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div v-if="announcement.file_path || announcement.image_path" class="flex items-center justify-center">
                                            <button @click="viewFile(announcement)"
                                                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 border rounded-lg font-bold transition cursor-pointer text-sm whitespace-nowrap"
                                                :class="expandedRows[announcement.id] ? 'bg-[var(--institutional-primary)] text-white border-[var(--institutional-primary)]' : 'text-[var(--institutional-primary)] border-[var(--institutional-primary)] hover:bg-[var(--institutional-primary)] hover:text-white'"
                                                title="Visualizar archivo">
                                                <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="currentColor">
                                                    <path v-if="!expandedRows[announcement.id]" d="M480-320q75 0 127.5-52.5T660-500q0-75-52.5-127.5T480-680q-75 0-127.5 52.5T300-500q0 75 52.5 127.5T480-320Zm0-72q-45 0-76.5-31.5T372-500q0-45 31.5-76.5T480-608q45 0 76.5 31.5T588-500q0 45-31.5 76.5T480-392Zm0 192q-146 0-266-81.5T40-500q54-137 174-218.5T480-800q146 0 266 81.5T920-500q-54 137-174 218.5T480-200Z"/>
                                                    <path v-else d="m644-428-58-58q9-47-27-88t-93-32l-58-58q17-8 34.5-12t37.5-4q75 0 127.5 52.5T660-500q0 20-4 37.5T644-428Zm128 126-58-56q38-29 67.5-63.5T832-500q-50-101-143.5-160.5T480-720q-29 0-57 4t-55 12l-62-62q41-17 84-25.5t90-8.5q151 0 269 83.5T920-500q-23 59-60.5 109.5T772-302Zm20 246L624-222q-35 11-70.5 16.5T480-200q-151 0-269-83.5T40-500q21-53 53-98.5t73-81.5L56-792l56-56 736 736-56 56ZM222-624q-29 26-53 57t-41 67q50 101 143.5 160.5T480-280q20 0 39-2.5t39-5.5l-36-38q-11 3-21 4.5t-21 1.5q-75 0-127.5-52.5T300-500q0-11 1.5-21t4.5-21l-84-82Zm319 93Zm-151 75Z"/>
                                                </svg>
                                                {{ expandedRows[announcement.id] ? 'Ocultar' : 'Visualizar' }}
                                            </button>
                                        </div>
                                        <button v-else disabled
                                            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 border border-gray-200 rounded-lg text-gray-400 font-bold cursor-not-allowed text-sm whitespace-nowrap bg-gray-50/50"
                                            title="Sin archivo">
                                            <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="currentColor">
                                                <path d="M480-320q75 0 127.5-52.5T660-500q0-75-52.5-127.5T480-680q-75 0-127.5 52.5T300-500q0 75 52.5 127.5T480-320Zm0-72q-45 0-76.5-31.5T372-500q0-45 31.5-76.5T480-608q45 0 76.5 31.5T588-500q0 45-31.5 76.5T480-392Zm0 192q-146 0-266-81.5T40-500q54-137 174-218.5T480-800q146 0 266 81.5T920-500q-54 137-174 218.5T480-200Z"/>
                                            </svg>
                                            Sin archivo
                                        </button>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-center gap-2">
                                            <Link v-if="useCan('announcements.edit')"
                                                :href="route(`${routeName}edit`, { announcement: announcement.id })"
                                                class="p-2 text-[var(--institutional-primary)] border border-[var(--institutional-primary)] rounded-full hover:bg-[var(--institutional-primary)] hover:text-white transition cursor-pointer" title="Editar">
                                                <svg xmlns="http://www.w3.org/2000/svg" height="20px"
                                                    viewBox="0 -960 960 960" width="20px" fill="currentColor">
                                                    <path
                                                        d="M200-200h57l391-391-57-57-391 391v57Zm-80 80v-170l528-527q12-11 26.5-17t30.5-6q16 0 31 6t26 18l55 56q12 11 17.5 26t5.5 30q0 16-5.5 30.5T817-647L290-120H120Zm640-584-56-56 56 56Zm-141 85-28-29 57 57-29-28Z" />
                                                </svg>
                                            </Link>
                                            <button v-if="useCan('announcements.delete')" type="button" aria-label="Eliminar convocatoria"
                                                @click="deleteAnnouncement(announcement.id)"
                                                class="p-2 text-red-600 border border-red-600 rounded-full hover:bg-red-600 hover:text-white transition cursor-pointer" title="Eliminar">
                                                <svg xmlns="http://www.w3.org/2000/svg" height="20px"
                                                    viewBox="0 -960 960 960" width="20px" fill="currentColor">
                                                    <path
                                                        d="M280-120q-33 0-56.5-23.5T200-200v-520h-40v-80h200v-40h240v40h200v80h-40v520q0 33-23.5 56.5T680-120H280Zm400-600H280v520h400v-520ZM360-280h80v-360h-80v360Zm160 0h80v-360h-80v360ZM280-720v520-520Z" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Inline Preview Row -->
                                <tr v-if="expandedRows[announcement.id]">
                                    <td colspan="5" class="px-6 py-6 bg-gray-50 border-b border-gray-200">
                                        <div class="flex flex-col gap-4 animate-in fade-in slide-in-from-top-4 duration-300">
                                            <div class="flex justify-between items-center">
                                                <h3 class="font-bold text-gray-800 text-lg">Vista Previa: {{ announcement.name }}</h3>
                                                <button @click="closeViewer(announcement.id)" class="text-gray-400 hover:text-gray-600 p-1.5 hover:bg-gray-100 rounded-lg transition cursor-pointer" title="Cerrar vista previa">
                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                            <div class="w-full h-[55vh] sm:h-[65vh] lg:h-[600px] border border-gray-300 rounded-xl overflow-hidden bg-white shadow-inner relative">
                                                <div class="absolute inset-0 flex items-center justify-center text-gray-400 z-0">
                                                    <div class="text-center">
                                                        <svg class="w-12 h-12 mx-auto animate-pulse mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                        </svg>
                                                        <span>Cargando vista previa...</span>
                                                    </div>
                                                </div>
                                                <iframe v-if="announcement.file_path && currentFile?.type?.includes('pdf')" 
                                                    :src="currentFile?.url" 
                                                    class="w-full h-full relative z-10" 
                                                    frameborder="0"
                                                ></iframe>
                                                <div v-else-if="announcement.file_path && currentFile?.type?.startsWith('image/')" class="w-full h-full flex items-center justify-center bg-gray-900 relative z-10">
                                                    <img :src="currentFile?.url" :alt="currentFile?.name" class="max-h-full max-w-full object-contain shadow-lg" />
                                                </div>
                                                <div v-else class="h-full flex items-center justify-center relative z-10 bg-white">
                                                    <div class="text-center p-12 text-gray-400">
                                                        <div class="bg-gray-50 p-6 rounded-full inline-block mb-4">
                                                            <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                            </svg>
                                                        </div>
                                                        <h4 class="text-xl font-bold text-gray-900 mb-2">Vista previa no disponible</h4>
                                                        <p class="mb-0 max-w-sm mx-auto">Este tipo de archivo no se puede visualizar directamente en el navegador.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    <Pagination :links="announcements.meta.links" :total="announcements.meta.total"
                        :from="announcements.meta.from" :to="announcements.meta.to" />
                </div>
            </div>

        </div>
    </LayoutAuthenticated>
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
